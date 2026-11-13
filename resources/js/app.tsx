import React, {
    createContext,
    useContext,
    useEffect,
    useRef,
    useState,
} from "react";
import { createRoot } from "react-dom/client";
import {
    BrowserRouter,
    Routes,
    Route,
    Link,
    useNavigate,
    useLocation,
    useSearchParams,
} from "react-router-dom";
import {
    Heart,
    Wallet,
    Gem,
    Headphones,
    Settings as SettingsIcon,
    Pencil,
    Search,
    ShoppingCart,
    UserRound,
    ChevronDown,
    ChevronRight,
    ArrowRight,
    ArrowLeft,
    Check,
    ShieldCheck,
    KeyRound,
    Zap,
    Globe,
    Gamepad2,
    Gift,
    Layers,
    X,
    Plus,
    Minus,
    Trash2,
    Copy,
    Menu,
    LogOut,
    CheckCircle2,
    LoaderCircle,
    SlidersHorizontal,
} from "lucide-react";
import "../css/app.css";
import "../css/eneba.css";
import type {
    Buyer,
    Cart as BuyerCart,
    Currency,
    GuestItem,
    Money as MoneyValue,
} from "./types";
class ApiError extends Error {
    status: number;
    fields: Record<string, string[]>;
}
type Shop = {
    user: Buyer | null;
    setUser: React.Dispatch<React.SetStateAction<Buyer | null>>;
    ready: boolean;
    bootstrap: any;
    cart: BuyerCart | null;
    currency: Currency;
    settings: any;
    settingsOpen: boolean;
    setSettingsOpen: (value: boolean) => void;
    changeSettings: (value: any) => void;
    notify: (message: string) => void;
    authenticate: (path: string, body: any) => Promise<Buyer>;
    logout: () => Promise<void>;
    refresh: (who?: Buyer) => Promise<BuyerCart>;
    mutate: (id: string, qty: number) => Promise<void>;
    add: (offer: any) => Promise<void>;
    mergeGuest: () => Promise<void>;
    completeGuestPurchase: (attempt: string, items: GuestItem[]) => void;
};

let csrf = "";
async function api(
    path: string,
    options: {
        method?: string;
        headers?: Record<string, string>;
        body?: any;
    } = {},
) {
    const method = options.method || "GET";
    if (method !== "GET" && !csrf) {
        const r = await fetch("/api/v1/auth/csrf", {
            headers: { Accept: "application/json" },
        });
        csrf = (await r.json()).data?.csrf_token || "";
    }
    let response;
    try {
        response = await fetch("/api/v1" + path, {
            ...options,
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                ...options.headers,
            },
            body: options.body ? JSON.stringify(options.body) : undefined,
        });
    } catch {
        throw new Error("Connection interrupted. Please try again.");
    }
    if (response.status === 204) return null;
    const result = await response.json();
    if (!response.ok) {
        if (response.status === 419) csrf = "";
        if (response.status === 401)
            window.dispatchEvent(new Event("session-expired"));
        const error = new ApiError(
            result.error?.fields && Object.keys(result.error.fields).length
                ? Object.values(result.error.fields).flat().join(" ")
                : result.error?.message ||
                      result.message ||
                      "Something went wrong. Please try again.",
        );
        error.status = response.status;
        error.fields = result.errors || result.error?.fields;
        throw error;
    }
    return result;
}
const read = (key, fallback) => {
    try {
        return JSON.parse(localStorage.getItem(key)) ?? fallback;
    } catch {
        return fallback;
    }
};
const save = (key, val) => localStorage.setItem(key, JSON.stringify(val));
const guestState = () => {
    const stored = read("guest-cart", []);
    return Array.isArray(stored)
        ? { items: stored, receipts: [] }
        : {
              items: Array.isArray(stored?.items) ? stored.items : [],
              receipts: Array.isArray(stored?.receipts) ? stored.receipts : [],
          };
};
const saveGuest = (items, receipts = guestState().receipts || []) =>
    save("guest-cart", { items, receipts });
const money = (m: MoneyValue | null) =>
    m
        ? new Intl.NumberFormat("en-IE", {
              style: "currency",
              currency: m.currency,
          }).format(Number(m.minor) / 100)
        : "Unavailable";
const Context = createContext<Shop>(null);
const useShop = () => useContext(Context);
function Provider({ children }) {
    const [settings, setSettings] = useState(() =>
        read("settings", {
            country: "Lithuania",
            language: "English",
            currency: "EUR",
        }),
    );
    const [user, setUser] = useState<Buyer | null>(null),
        [ready, setReady] = useState(false),
        [bootstrap, setBootstrap] = useState(null),
        [cart, setCart] = useState<BuyerCart | null>(null),
        [notice, setNotice] = useState(""),
        [settingsOpen, setSettingsOpen] = useState(false);
    const currency: Currency = settings.currency;
    const [guest, setGuest] = useState<GuestItem[]>(() => guestState().items);
    const guestRef = useRef(guest);
    const activeRef = useRef<Buyer | null>(null);
    const cartRequest = useRef(0);
    useEffect(() => {
        guestRef.current = guest;
        saveGuest(guest);
    }, [guest]);
    useEffect(() => {
        const expire = () => {
            activeRef.current = null;
            setUser(null);
            setCart(null);
            csrf = "";
        };
        window.addEventListener("session-expired", expire);
        return () => window.removeEventListener("session-expired", expire);
    }, []);
    const notify = (msg) => setNotice(msg);
    useEffect(() => {
        if (notice) {
            const t = setTimeout(() => setNotice(""), 4500);
            return () => clearTimeout(t);
        }
    }, [notice]);
    const refresh = async (who = activeRef.current) => {
        const request = ++cartRequest.current;
        const result = who
            ? await api(`/cart?currency=${currency}`)
            : await api("/cart/preview", {
                  method: "POST",
                  body: { items: guestRef.current, currency },
              });
        if (request === cartRequest.current) setCart(result.data);
        return result.data;
    };
    useEffect(() => {
        Promise.all([
            api("/bootstrap"),
            api("/me").catch((e) => {
                if (e.status !== 401) throw e;
                return { data: null };
            }),
        ])
            .then(([b, u]) => {
                setBootstrap(b.data);
                setUser(u.data);
                activeRef.current = u.data;
                setReady(true);
            })
            .catch((e) => {
                notify(e.message);
                setReady(true);
            });
    }, []);
    useEffect(() => {
        if (ready) refresh().catch((e) => notify(e.message));
    }, [ready, currency, user?.id, guest]);
    useEffect(() => {
        const handler = (e) => {
            if (e.key === "guest-cart") setGuest(guestState().items);
            if (e.key === "settings") setSettings(read("settings", settings));
        };
        window.addEventListener("storage", handler);
        return () => window.removeEventListener("storage", handler);
    }, []);
    const authenticate = async (path, body) => {
        const r = await api("/auth/" + path, { method: "POST", body });
        setUser(r.data);
        activeRef.current = r.data;
        csrf = "";
        await mergeGuest();
        return r.data;
    };
    const mergeGuest = async () => {
        const pending = read("pending-merge", null);
        const items = pending?.items || guestRef.current;
        if (!items.length) return;
        const transfer = pending || { transfer_id: crypto.randomUUID(), items };
        save("pending-merge", transfer);
        await api("/cart/merge", {
            method: "POST",
            body: { ...transfer, currency },
        });
        localStorage.removeItem("pending-merge");
        saveGuest([]);
        guestRef.current = [];
        setGuest([]);
    };
    const logout = async () => {
        await api("/auth/logout", { method: "POST" });
        csrf = "";
        setUser(null);
        activeRef.current = null;
        setCart(null);
    };
    const mutate = async (offer, quantity) => {
        if (user) {
            try {
                const r = await api("/cart/items/" + offer, {
                    method: quantity ? "PUT" : "DELETE",
                    body: {
                        quantity,
                        expected_revision: cart.revision,
                        currency,
                    },
                });
                setCart(r.data);
            } catch (e) {
                await refresh();
                throw e;
            }
        } else {
            const next = guestRef.current.filter((i) => i.offer_id !== offer);
            if (quantity) next.push({ offer_id: offer, quantity });
            saveGuest(next);
            guestRef.current = next;
            setGuest(next);
        }
    };
    const add = async (offer) => {
        const qty =
            cart?.items.find((i) => i.offer_id === offer.id)?.quantity || 0;
        if (qty >= offer.stock_quantity)
            throw new Error(
                "You have reached the available stock for this offer.",
            );
        await mutate(offer.id, qty + 1);
        notify("Added to your cart");
    };
    const completeGuestPurchase = (attempt, items) => {
        const stored = guestState();
        if (stored.receipts?.includes(attempt)) return;
        const bought = new Map(items.map((i) => [i.offer_id, i.quantity]));
        const next = stored.items
            .map((i) => ({
                ...i,
                quantity: Math.max(
                    0,
                    i.quantity - Number(bought.get(i.offer_id) || 0),
                ),
            }))
            .filter((i) => i.quantity > 0);
        saveGuest(next, [...(stored.receipts || []), attempt].slice(-100));
        guestRef.current = next;
        setGuest(next);
    };
    const changeSettings = (next) => {
        save("settings", next);
        setSettings(next);
        setSettingsOpen(false);
    };
    return (
        <Context.Provider
            value={{
                user,
                setUser,
                ready,
                bootstrap,
                cart,
                currency,
                settings,
                settingsOpen,
                setSettingsOpen,
                changeSettings,
                notify,
                authenticate,
                logout,
                refresh,
                mutate,
                add,
                mergeGuest,
                completeGuestPurchase,
            }}
        >
            {children}
            {notice && (
                <div className="toast" role="status">
                    <CheckCircle2 size={19} />
                    {notice}
                    <button
                        aria-label="Dismiss notification"
                        onClick={() => setNotice("")}
                    >
                        <X size={16} />
                    </button>
                </div>
            )}
            {settingsOpen && <Settings />}
        </Context.Provider>
    );
}
function Logo() {
    return (
        <Link className="logo" to="/" aria-label="Eneba homepage">
            <span className="logo-mark">
                <i />
                <i />
                <i />
            </span>
            <span>eneba</span>
            <small>DEMO</small>
        </Link>
    );
}
function GlobalSearch() {
    const { currency } = useShop();
    const navigate = useNavigate();
    const [term, setTerm] = useState(""),
        [suggestions, setSuggestions] = useState([]),
        [suggestionsLoading, setSuggestionsLoading] = useState(false),
        [focus, setFocus] = useState(false);
    const location = useLocation();
    useEffect(() => {
        setFocus(false);
    }, [location.pathname, location.search]);
    useEffect(() => {
        if (term.trim().length < 1) {
            setSuggestions([]);
            setSuggestionsLoading(false);
            return;
        }
        let stale = false;
        setSuggestions([]);
        setSuggestionsLoading(true);
        const timer = setTimeout(
            () =>
                api(
                    `/search/suggestions?q=${encodeURIComponent(term)}&currency=${currency}`,
                )
                    .then((r) => {
                        if (!stale) {
                            setSuggestions(r.data.items);
                            setSuggestionsLoading(false);
                        }
                    })
                    .catch(() => {
                        if (!stale) {
                            setSuggestions([]);
                            setSuggestionsLoading(false);
                        }
                    }),
            220,
        );
        return () => {
            stale = true;
            clearTimeout(timer);
        };
    }, [term, currency]);
    return (
        <div
            className="search-wrap"
            onBlur={(e) => {
                if (!e.currentTarget.contains(e.relatedTarget as Node))
                    setFocus(false);
            }}
            onKeyDown={(e) => {
                if (e.key === "Escape") {
                    setFocus(false);
                    return;
                }
                if (!["ArrowDown", "ArrowUp"].includes(e.key)) return;
                const links = Array.from(
                    e.currentTarget.querySelectorAll<HTMLAnchorElement>(
                        ".suggestions a",
                    ),
                );
                if (!links.length) return;
                e.preventDefault();
                const current = links.indexOf(
                    document.activeElement as HTMLAnchorElement,
                );
                links[
                    (current +
                        (e.key === "ArrowDown" ? 1 : links.length - 1) +
                        links.length) %
                        links.length
                ].focus();
            }}
        >
            <form
                className="search-box"
                role="search"
                onSubmit={(e) => {
                    e.preventDefault();
                    setFocus(false);
                    navigate("/catalog?q=" + encodeURIComponent(term));
                }}
            >
                <Search size={21} />
                <input
                    aria-label="Search products"
                    type="search"
                    aria-controls="search-suggestions"
                    placeholder="Search for games, gift cards and more"
                    value={term}
                    onFocus={() => setFocus(true)}
                    onChange={(e) => {
                        setTerm(e.target.value);
                        setFocus(true);
                    }}
                />
                <kbd>↵</kbd>
            </form>
            {focus && term.trim().length >= 1 && (
                <div
                    className="suggestions"
                    id="search-suggestions"
                    role="region"
                    aria-label="Search suggestions"
                    aria-busy={suggestionsLoading}
                >
                    <span className="eyebrow">QUICK RESULTS</span>
                    {suggestions.map((p) => (
                        <Link key={p.id} to={"/products/" + p.slug}>
                            <img src={p.image.url} alt="" />
                            <span>
                                {p.title}
                                <small>
                                    {p.platform.name} · {p.region.name}
                                </small>
                            </span>
                            <b>{money(p.starting_price)}</b>
                        </Link>
                    ))}
                    {suggestionsLoading && (
                        <p role="status">Finding matches…</p>
                    )}
                    {!suggestionsLoading && !suggestions.length && (
                        <p>No matches yet. Try another title.</p>
                    )}
                    <Link
                        className="all-results"
                        to={"/catalog?q=" + encodeURIComponent(term)}
                    >
                        See all results <ArrowRight size={16} />
                    </Link>
                </div>
            )}
        </div>
    );
}
function Header() {
    const { user, cart, settings, setSettingsOpen, currency } = useShop();
    const location = useLocation();
    if (
        location.pathname.startsWith("/account") ||
        location.pathname === "/library" ||
        location.pathname === "/orders"
    )
        return <AccountHeader />;
    return (
        <>
            <div className="announcement">
                A world of play, a better price.{" "}
                <span>
                    Find your next favourite <ArrowRight size={12} />
                </span>
            </div>
            <header>
                <div className="header-main container">
                    <Logo />
                    <GlobalSearch />
                    <button
                        className="locale"
                        onClick={() => setSettingsOpen(true)}
                    >
                        <span aria-hidden="true">
                            {settings.country === "United States"
                                ? "🇺🇸"
                                : settings.country === "United Kingdom"
                                  ? "🇬🇧"
                                  : "🇱🇹"}
                        </span>
                        <span>English · {settings.currency}</span>
                        <ChevronDown size={12} />
                    </button>
                    <div className="header-actions">
                        <Link to="/preview/favorites" aria-label="Favorites">
                            <Heart size={24} />
                        </Link>
                        <Link
                            to="/cart"
                            className="cart-icon"
                            aria-label={`Shopping cart, ${cart?.items.reduce((n, i) => n + i.quantity, 0) || 0} items`}
                        >
                            <ShoppingCart size={24} />
                            {!!cart?.items.length && (
                                <b>
                                    {cart.items.reduce(
                                        (n, i) => n + i.quantity,
                                        0,
                                    )}
                                </b>
                            )}
                        </Link>
                        <ProfileMenu />
                    </div>
                </div>
                <nav className="container nav">
                    <Link to="/catalog">
                        <Menu size={16} /> All categories
                    </Link>
                    <i />
                    <Link to="/catalog?type=game">Games</Link>
                    <Link to="/catalog?type=gift_card">Gift cards</Link>
                    <Link to="/catalog?type=subscription">Subscriptions</Link>
                    <Link to="/catalog?platform=steam">PC</Link>
                    <Link to="/catalog?platform=playstation">PlayStation</Link>
                    <Link to="/catalog?platform=xbox">Xbox</Link>
                    <span className="nav-promo">
                        Small prices. Big adventures. <Zap size={14} />
                    </span>
                </nav>
            </header>
        </>
    );
}
function Footer() {
    const { setSettingsOpen, currency } = useShop();
    return (
        <footer>
            <div className="container">
                <div className="footer-top">
                    <Logo />
                    <p>Your next adventure is closer than you think.</p>
                    <div className="payments">
                        <b>VISA</b>
                        <b>mastercard</b>
                        <b>PayPal</b>
                        <b>Wallet</b>
                    </div>
                </div>
                <div className="footer-links expanded-footer">
                    <div>
                        <b>About Eneba</b>
                        <a
                            href="https://www.eneba.com/about-us"
                            target="_blank"
                            rel="noreferrer"
                        >
                            About us ↗
                        </a>
                        <a
                            href="https://www.eneba.com/contacts"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Contact Eneba ↗
                        </a>
                        <a
                            href="https://jobs.ashbyhq.com/eneba"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Careers ↗
                        </a>
                    </div>
                    <div>
                        <b>Buy</b>
                        <Link to="/catalog?type=game">Games</Link>
                        <Link to="/catalog?type=gift_card">
                            Gaming gift cards
                        </Link>
                        <Link to="/catalog?type=subscription">
                            Subscriptions
                        </Link>
                        <Link to="/catalog?price_max_minor=1000">
                            Cheap games
                        </Link>
                    </div>
                    <div>
                        <b>Your account</b>
                        <Link to="/account">My account</Link>
                        <Link to="/orders">Orders</Link>
                        <Link to="/library">Keys library</Link>
                        <Link to="/cart">Shopping cart</Link>
                    </div>
                    <div>
                        <b>Help</b>
                        <a
                            href="https://www.eneba.com/support/activation-guides"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Activation guides ↗
                        </a>
                        <Link to="/preview/tickets">
                            Support · demo preview
                        </Link>
                        <a
                            href="https://www.eneba.com/support"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Eneba FAQ ↗
                        </a>
                    </div>
                    <div>
                        <b>Follow Eneba</b>
                        <div className="social-links">
                            <a
                                href="https://www.instagram.com/enebagames/"
                                target="_blank"
                                rel="noreferrer"
                            >
                                Instagram ↗
                            </a>
                            <a
                                href="https://x.com/eneba_games"
                                target="_blank"
                                rel="noreferrer"
                            >
                                X ↗
                            </a>
                            <a
                                href="https://www.reddit.com/r/Enebagames/"
                                target="_blank"
                                rel="noreferrer"
                            >
                                Reddit ↗
                            </a>
                        </div>
                    </div>
                    <div>
                        <b>Discover the Eneba app</b>
                        <a
                            className="app-store-link"
                            href="https://play.google.com/store/apps/details?id=com.eneba.app"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Google Play ↗
                        </a>
                        <a
                            className="app-store-link"
                            href="https://apps.apple.com/app/id1564406362"
                            target="_blank"
                            rel="noreferrer"
                        >
                            App Store ↗
                        </a>
                    </div>
                </div>
                <div className="newsletter-preview">
                    <div>
                        <b>Get personalized game deals</b>
                        <p>
                            Newsletter preview — subscriptions are not collected
                            in this demo.
                        </p>
                        <div>
                            <input
                                aria-label="Newsletter email preview"
                                placeholder="Enter your email"
                                disabled
                            />
                            <button disabled>Subscribe</button>
                        </div>
                    </div>
                    <p className="demo-note">
                        Internship demonstration. Payments and keys are
                        simulated. Cashback, balances, loyalty, ratings and
                        reviews are sample content. Links marked ↗ open the real
                        external service.
                    </p>
                </div>
                <div className="footer-bottom">
                    <span>
                        © 2026 Eneba-style marketplace · Daraab Pathania
                    </span>
                    <button onClick={() => setSettingsOpen(true)}>
                        English · {currency} <ChevronDown size={12} />
                    </button>
                </div>
            </div>
        </footer>
    );
}
function Settings() {
    const { settings, changeSettings, setSettingsOpen } = useShop();
    const [draft, setDraft] = useState(settings);
    const dialog = useRef<HTMLDialogElement>(null);
    useEffect(() => {
        dialog.current.showModal();
        return () => dialog.current?.close();
    }, []);
    return (
        <dialog
            ref={dialog}
            onCancel={() => setSettingsOpen(false)}
            className="settings"
        >
            <button
                className="close"
                aria-label="Close settings"
                onClick={() => setSettingsOpen(false)}
            >
                <X />
            </button>
            <Globe className="accent" size={28} />
            <h2>Make yourself at home</h2>
            <p>Choose your region, language and currency.</p>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    changeSettings(draft);
                }}
            >
                {(
                    [
                        [
                            "country",
                            "Region",
                            ["Lithuania", "United States", "United Kingdom"],
                        ],
                        ["language", "Language", ["English"]],
                        ["currency", "Currency", ["EUR", "USD", "GBP"]],
                    ] as const
                ).map(([key, label, opts]) => (
                    <label key={key}>
                        {label}
                        <select
                            value={draft[key]}
                            onChange={(e) =>
                                setDraft({ ...draft, [key]: e.target.value })
                            }
                        >
                            {opts.map((o) => (
                                <option key={o}>{o}</option>
                            ))}
                        </select>
                    </label>
                ))}
                <p className="muted small">
                    English content throughout. Region is a preference; product
                    activation regions remain unchanged. Prices use fixed demo
                    exchange rates.
                </p>
                <div className="button-row">
                    <button
                        type="button"
                        className="secondary"
                        onClick={() => setSettingsOpen(false)}
                    >
                        Cancel
                    </button>
                    <button className="primary">Save preferences</button>
                </div>
            </form>
        </dialog>
    );
}
function Loading() {
    return (
        <div className="loading" role="status">
            <LoaderCircle className="spin" /> Finding something good…
        </div>
    );
}
function ErrorBox({ error, retry = null }) {
    return (
        <div className="error" role="alert">
            {error}
            {retry && <button onClick={retry}>Try again</button>}
        </div>
    );
}
function useLoad(path, deps = []) {
    const [state, setState] = useState<{
        loading: boolean;
        data: any;
        meta?: any;
        error: string;
    }>({ loading: true, data: null, error: "" });
    const [retry, setRetry] = useState(0);
    useEffect(() => {
        let stale = false;
        setState((s) => ({ ...s, loading: true, error: "" }));
        api(path)
            .then((r) => {
                if (!stale)
                    setState({
                        loading: false,
                        data: r.data,
                        meta: r.meta,
                        error: "",
                    });
            })
            .catch((e) => {
                if (!stale)
                    setState({ loading: false, data: null, error: e.message });
            });
        return () => {
            stale = true;
        };
    }, [path, retry, ...deps]);
    return { ...state, retry: () => setRetry((n) => n + 1) };
}
function Card({ p }) {
    return (
        <Link className="product-card" to={"/products/" + p.slug}>
            <div className="cover">
                <img
                    loading="lazy"
                    src={p.image.url}
                    alt={p.image.alt}
                    onError={(e) => {
                        e.currentTarget.onerror = null;
                        e.currentTarget.src = "/images/fallback.svg";
                    }}
                />
                <span className="cashback">
                    <Zap size={11} fill="currentColor" />{" "}
                    {Number(p.demo_cashback.percent)}% CASHBACK*
                </span>
            </div>
            <div className="platform-strip">
                <Gamepad2 size={12} />
                {p.platform.name}
            </div>
            <div className="card-body">
                <h3>{p.title}</h3>
                <span className="region">{p.region.name}</span>
                <small>From</small>
                <strong>{money(p.starting_price)}</strong>
                <span className="cashback-text">
                    {p.availability === "sold_out"
                        ? "Currently sold out"
                        : "Digital key · instant delivery"}
                </span>
            </div>
        </Link>
    );
}
function Rail({ title, items, href = "/catalog", eyebrow = null }) {
    if (!items.length) return null;
    return (
        <section className="rail container">
            <div className="section-heading">
                <div>
                    {eyebrow && <span className="eyebrow">{eyebrow}</span>}
                    <h2>{title}</h2>
                </div>
            </div>
            <div className="product-grid">
                {items.map((p) => (
                    <Card key={p.id} p={p} />
                ))}
            </div>
            <Link className="secondary rail-more" to={href}>
                Show all <ArrowRight size={15} />
            </Link>
        </section>
    );
}
function HomeBlock({ block }) {
    const { currency } = useShop();
    const [slide, setSlide] = useState(0);
    const recent = read("recent-products", []);
    const ids = block.requires_history
        ? recent.map((id) => "ids[]=" + encodeURIComponent(id)).join("&")
        : "";
    const state = useLoad(
        block.content_url.replace("/api/v1", "") +
            "?currency=" +
            currency +
            "&" +
            ids,
    );
    if (state.loading)
        return (
            <div
                className="container rail skeleton-rail"
                aria-label="Loading products"
            />
        );
    if (state.error)
        return (
            <div className="container">
                <ErrorBox error={state.error} retry={state.retry} />
            </div>
        );
    const b = state.data;
    const promotion = b.configuration.promotions?.[slide] || b.configuration;
    if (b.renderer_type === "hero")
        return (
            <section
                className="hero container"
                aria-label="Marketplace promotions"
            >
                <div
                    className="hero-art"
                    style={{
                        backgroundImage: `url('${promotion.image}')`,
                    }}
                />
                <div className="hero-copy">
                    <span className="eyebrow">
                        <span className="green-dot" /> {b.configuration.eyebrow}
                    </span>
                    <h1>
                        {promotion.heading_lines.map((line, index) => (
                            <React.Fragment key={index}>
                                {line} <br />
                            </React.Fragment>
                        ))}
                        <span>{promotion.heading_accent}</span>
                    </h1>
                    <p>{promotion.subtitle}</p>
                    <Link className="primary" to={promotion.cta_url}>
                        {promotion.cta_label} <ArrowRight size={19} />
                    </Link>
                    <span className="hero-caption">
                        {b.configuration.caption}
                    </span>
                </div>
                <div className="hero-tag">
                    <Gamepad2 size={18} /> {b.configuration.tagline}
                </div>
                <div className="hero-dots">
                    {(b.configuration.promotions || [b.configuration]).map(
                        (_, n) => (
                            <button
                                key={n}
                                aria-label={`Show promotion ${n + 1}`}
                                aria-pressed={slide === n}
                                className={slide === n ? "selected" : ""}
                                onClick={() => setSlide(n)}
                            />
                        ),
                    )}
                </div>
            </section>
        );
    if (b.renderer_type === "genres") return <GenreTiles block={b} />;
    if (b.renderer_type === "shortcuts")
        return (
            <>
                <DiscoveryStrip links={b.configuration.links || []} />
                <div className="container trust-strip">
                    {b.configuration.trust_items?.map(({ icon, label }) => {
                        const Icon =
                            {
                                shield: ShieldCheck,
                                key: KeyRound,
                                globe: Globe,
                                zap: Zap,
                            }[icon] || ShieldCheck;
                        return (
                            <span key={label}>
                                <Icon />
                                {label}
                            </span>
                        );
                    })}
                </div>
            </>
        );
    return (
        <Rail
            title={b.title}
            items={b.items}
            eyebrow={b.configuration.eyebrow}
            href={b.configuration.cta_url || "/catalog"}
        />
    );
}
function Home() {
    const state = useLoad("/homepage/sections");
    return (
        <main className="home">
            {state.loading ? (
                <Loading />
            ) : state.error ? (
                <ErrorBox error={state.error} retry={state.retry} />
            ) : (
                <>
                    {state.data.map((b) => (
                        <HomeBlock key={b.key} block={b} />
                    ))}
                </>
            )}
        </main>
    );
}
function Catalog() {
    const { bootstrap, currency } = useShop();
    const [params, setParams] = useSearchParams();
    const query = new URLSearchParams();
    query.set("currency", currency);
    for (const [k, v] of params) {
        if (["type", "platform", "region", "genre"].includes(k))
            query.append(k + "[]", v);
        else query.append(k, v);
    }
    const state = useLoad("/products?" + query);
    const [filtersOpen, setFiltersOpen] = useState(false);
    const update = (key, value, multi = false) => {
        const p = new URLSearchParams(params);
        if (multi) {
            const values = p.getAll(key);
            p.delete(key);
            for (const v of values.includes(value)
                ? values.filter((x) => x !== value)
                : [...values, value])
                p.append(key, v);
        } else {
            if (value) p.set(key, value);
            else p.delete(key);
        }
        if (key !== "page") p.delete("page");
        setParams(p);
    };
    const [min, setMin] = useState(
            params.get("price_min_minor")
                ? Number(params.get("price_min_minor")) / 100
                : "",
        ),
        [max, setMax] = useState(
            params.get("price_max_minor")
                ? Number(params.get("price_max_minor")) / 100
                : "",
        );
    return (
        <main className="container catalog-page">
            <div className="breadcrumbs">
                <Link to="/">Store</Link>
                <ChevronRight size={12} />
                Discover
            </div>
            <div className="page-heading">
                <div>
                    <span className="eyebrow">FIND YOUR NEXT FAVOURITE</span>
                    <h1>
                        {params.get("q")
                            ? `Results for “${params.get("q")}”`
                            : "Explore the store"}
                    </h1>
                    <p>
                        {state.meta?.total ?? "54"} digital products. Endless
                        possibilities.
                    </p>
                </div>
                <button
                    className="secondary mobile-filter"
                    onClick={() => setFiltersOpen(!filtersOpen)}
                >
                    <SlidersHorizontal size={16} /> Filters
                </button>
            </div>
            <div className="catalog-layout">
                <aside className={"filters " + (filtersOpen ? "open" : "")}>
                    <div className="filter-heading">
                        <b>Filter products</b>
                        <button
                            onClick={() => {
                                setParams({});
                                setMin("");
                                setMax("");
                            }}
                        >
                            Clear all
                        </button>
                    </div>
                    {[
                        ["type", "Product type", bootstrap?.types],
                        ["platform", "Platform", bootstrap?.platforms],
                        ["region", "Activation region", bootstrap?.regions],
                        ["genre", "Genre", bootstrap?.genres],
                    ].map(([key, label, opts]) => (
                        <fieldset key={key}>
                            <legend>{label}</legend>
                            {opts?.map((o) => (
                                <label className="check-label" key={o.code}>
                                    <input
                                        type="checkbox"
                                        checked={params
                                            .getAll(key)
                                            .includes(o.code)}
                                        onChange={() =>
                                            update(key, o.code, true)
                                        }
                                    />
                                    {o.name}
                                </label>
                            ))}
                        </fieldset>
                    ))}
                    <fieldset>
                        <legend>Price ({currency})</legend>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                const p = new URLSearchParams(params);
                                for (const [key, val] of [
                                    ["price_min_minor", min],
                                    ["price_max_minor", max],
                                ]) {
                                    if (val !== "")
                                        p.set(
                                            String(key),
                                            String(
                                                Math.round(Number(val) * 100),
                                            ),
                                        );
                                    else p.delete(String(key));
                                }
                                p.delete("page");
                                setParams(p);
                            }}
                        >
                            <div className="price-inputs">
                                <input
                                    aria-label="Minimum price"
                                    placeholder="Min"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={min}
                                    onChange={(e) => setMin(e.target.value)}
                                />
                                <span>–</span>
                                <input
                                    aria-label="Maximum price"
                                    placeholder="Max"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={max}
                                    onChange={(e) => setMax(e.target.value)}
                                />
                            </div>
                            <button className="secondary full">
                                Apply price
                            </button>
                        </form>
                    </fieldset>
                </aside>
                <div className="catalog-results">
                    <div className="results-toolbar">
                        <span>{state.meta?.total ?? "…"} results</span>
                        <label>
                            Sort by{" "}
                            <select
                                aria-label="Sort products"
                                value={
                                    params.get("sort") ||
                                    (params.get("q")
                                        ? "relevance"
                                        : "popularity")
                                }
                                onChange={(e) => update("sort", e.target.value)}
                            >
                                {params.get("q") && (
                                    <option value="relevance">Relevance</option>
                                )}
                                {[
                                    ["popularity", "Most popular"],
                                    ["price_asc", "Price: low to high"],
                                    ["price_desc", "Price: high to low"],
                                    ["title_asc", "Title: A–Z"],
                                    ["title_desc", "Title: Z–A"],
                                    ["date_desc", "Newest first"],
                                    ["date_asc", "Oldest first"],
                                ].map(([v, t]) => (
                                    <option value={v} key={v}>
                                        {t}
                                    </option>
                                ))}
                            </select>
                        </label>
                    </div>
                    {state.loading ? (
                        <Loading />
                    ) : state.error ? (
                        <ErrorBox error={state.error} retry={state.retry} />
                    ) : state.data.length ? (
                        <>
                            <div className="product-grid">
                                {state.data.map((p) => (
                                    <Card key={p.id} p={p} />
                                ))}
                            </div>
                            <nav
                                className="pagination"
                                aria-label="Catalogue pages"
                            >
                                {Array.from(
                                    { length: state.meta.last_page },
                                    (_, i) => i + 1,
                                ).map((n) => (
                                    <button
                                        key={n}
                                        aria-current={
                                            state.meta.page === n
                                                ? "page"
                                                : undefined
                                        }
                                        className={
                                            state.meta.page === n
                                                ? "active"
                                                : ""
                                        }
                                        onClick={() => {
                                            update("page", String(n));
                                            window.scrollTo({
                                                top: 180,
                                                behavior: "smooth",
                                            });
                                        }}
                                    >
                                        {n}
                                    </button>
                                ))}
                            </nav>
                        </>
                    ) : (
                        <div className="empty">
                            <Search size={38} />
                            <h2>No products found</h2>
                            <p>
                                Try a different search or clear a few filters.
                            </p>
                            <button
                                className="primary"
                                onClick={() => setParams({})}
                            >
                                Clear filters
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </main>
    );
}
function OfferRow({ offer, onBuy }) {
    return (
        <div className="offer-row">
            <span className="seller-avatar">{offer.seller.name[0]}</span>
            <div className="seller-name">
                <b>
                    {offer.seller.name} <ShieldCheck size={13} />
                </b>
                <small>
                    <span className="stars">★</span>{" "}
                    {offer.seller.rating.score.toFixed(2)} · Excellent{" "}
                    <span className="muted">(sample)</span>
                </small>
            </div>
            <div className="offer-price">
                <small>{money(offer.compare_at_price)}</small>
                <strong>{money(offer.unit_price)}</strong>
                <span>{offer.stock_quantity} in stock</span>
            </div>
            <button
                className="secondary"
                disabled={!offer.is_purchasable}
                onClick={() => onBuy(offer)}
            >
                Buy now
            </button>
        </div>
    );
}
function Product() {
    const { currency, add, notify } = useShop();
    const location = useLocation();
    const slug = location.pathname.split("/").pop();
    const detail = useLoad(`/products/${slug}?currency=${currency}`),
        offers = useLoad(`/products/${slug}/offers?currency=${currency}`);
    const [expanded, setExpanded] = useState(false),
        [scene, setScene] = useState(null),
        [busy, setBusy] = useState(false);
    const navigate = useNavigate();
    useEffect(() => {
        setScene(null);
        setExpanded(false);
        if (detail.data) {
            save(
                "recent-products",
                [
                    detail.data.id,
                    ...read("recent-products", []).filter(
                        (i) => i !== detail.data.id,
                    ),
                ].slice(0, 10),
            );
            document.title = detail.data.title + " · Eneba";
        }
    }, [detail.data?.id]);
    const buy = async (offer, now = true) => {
        if (busy) return;
        setBusy(true);
        try {
            await add(offer);
            if (now) navigate("/checkout");
        } catch (e) {
            notify(e.message);
        } finally {
            setBusy(false);
        }
    };
    if (detail.loading) return <Loading />;
    if (detail.error)
        return (
            <main className="container">
                <ErrorBox error={detail.error} retry={detail.retry} />
            </main>
        );
    const p = detail.data;
    const o = offers.data;
    const featured =
        [...(o?.recommended || []), ...(o?.other || [])].find(
            (x) => x.id === o?.featured_offer_id,
        ) || o?.other?.[0];
    return (
        <main className="container product-page">
            <div className="breadcrumbs">
                <Link to="/">Store</Link>
                <ChevronRight size={12} />
                <Link to={"/catalog?type=" + p.type}>
                    {p.type === "game"
                        ? "Games"
                        : p.type === "gift_card"
                          ? "Gift cards"
                          : "Subscriptions"}
                </Link>
                <ChevronRight size={12} />
                {p.platform.name}
            </div>
            <div className="product-layout">
                <div className="product-main">
                    <div className="product-intro">
                        <img
                            className="main-cover"
                            src={p.image.url}
                            alt={p.title}
                        />
                        <div>
                            <span className="pill">
                                <Zap size={12} /> Cashback deal · visual demo
                            </span>
                            <h1>
                                {p.title} {p.platform.name} Key{" "}
                                <span>{p.region.name.toUpperCase()}</span>
                            </h1>
                            <div className="product-facts">
                                <div>
                                    <Globe />
                                    <span>
                                        <b>{p.region.name}</b>
                                        <small>
                                            Activation region restriction
                                        </small>
                                    </span>
                                </div>
                                <div>
                                    <Gamepad2 />
                                    <span>
                                        <b>{p.platform.name}</b>
                                        <small>Redeem on your platform</small>
                                    </span>
                                </div>
                                <div>
                                    <KeyRound />
                                    <span>
                                        <b>Digital key</b>
                                        <small>
                                            Simulated code · no physical item
                                        </small>
                                    </span>
                                </div>
                                <div>
                                    <Zap />
                                    <span>
                                        <b>Instant delivery</b>
                                        <small>
                                            View keys after demo checkout
                                        </small>
                                    </span>
                                </div>
                            </div>
                            {p.gift_card && (
                                <p className="muted">
                                    Face value: {money(p.gift_card.face_value)}
                                </p>
                            )}
                            {p.subscription && (
                                <p className="muted">
                                    {p.subscription.duration_months}-month
                                    prepaid membership · no renewal
                                </p>
                            )}
                        </div>
                    </div>
                    {p.gallery.length > 1 && (
                        <div className="gallery">
                            {p.gallery
                                .filter((i) => i.position > 0)
                                .map((i) => (
                                    <button
                                        key={i.url}
                                        aria-label={"View " + i.alt}
                                        onClick={() => setScene(i)}
                                    >
                                        <img src={i.url} alt={i.alt} />
                                    </button>
                                ))}
                        </div>
                    )}
                    {scene && (
                        <div className="image-preview">
                            <button
                                className="close"
                                aria-label="Close image"
                                onClick={() => setScene(null)}
                            >
                                <X />
                            </button>
                            <img src={scene.url} alt={scene.alt} />
                        </div>
                    )}
                    <section className="offers">
                        <div className="section-heading">
                            <h2>Recommended offers</h2>
                            <span className="muted small">
                                Compare and choose your seller
                            </span>
                        </div>
                        {offers.loading ? (
                            <Loading />
                        ) : offers.error ? (
                            <ErrorBox
                                error={offers.error}
                                retry={offers.retry}
                            />
                        ) : (
                            <div aria-busy={busy}>
                                {o.recommended.map((x) => (
                                    <OfferRow
                                        key={x.id}
                                        offer={x}
                                        onBuy={buy}
                                    />
                                ))}
                                <h3 className="other-title">
                                    {o.other.length} other offers{" "}
                                    <span>
                                        from {money(o.other[0]?.unit_price)}
                                    </span>
                                </h3>
                                {o.other
                                    .slice(0, expanded ? 100 : 3)
                                    .map((x) => (
                                        <OfferRow
                                            key={x.id}
                                            offer={x}
                                            onBuy={buy}
                                        />
                                    ))}
                                {!expanded && o.other.length > 3 && (
                                    <button
                                        className="secondary show-more"
                                        onClick={() => setExpanded(true)}
                                    >
                                        Show all {o.total_offers} offers{" "}
                                        <ChevronDown size={14} />
                                    </button>
                                )}
                            </div>
                        )}
                    </section>
                    <section className="product-description">
                        <h2>About this product</h2>
                        <p>{p.description}</p>
                        <div className="info-note">
                            <ShieldCheck size={20} />
                            <p>
                                Before you buy, check that the platform and
                                activation region match your account. Demo keys
                                cannot be activated.
                            </p>
                        </div>
                    </section>
                    <section className="reviews">
                        <div className="section-heading">
                            <h2>What players are saying</h2>
                            <span className="pill">Sample reviews</span>
                        </div>
                        {p.reviews.items.map((r) => (
                            <div className="review" key={r.id}>
                                <span className="seller-avatar">
                                    {r.display_author[0]}
                                </span>
                                <div>
                                    <b>{r.display_author}</b>
                                    <span className="stars">
                                        {"★".repeat(r.rating)}
                                    </span>
                                    <p>{r.body}</p>
                                </div>
                            </div>
                        ))}
                    </section>
                </div>
                <aside className="featured-panel">
                    <div className="rating">
                        <span>Excellent</span>
                        <b>★★★★★</b>
                        <small>Sample seller ratings</small>
                    </div>
                    <div className="featured-box">
                        <span className="eyebrow">FEATURED OFFER</span>
                        {featured ? (
                            <>
                                <small className="strike">
                                    {money(featured.compare_at_price)}
                                </small>
                                <strong>{money(featured.unit_price)}</strong>
                                <p className="cashback-text">
                                    {p.demo_cashback.percent}% cashback · visual
                                    demo
                                </p>
                                <p className="small muted">
                                    Sold by {featured.seller.name}
                                </p>
                                <button
                                    className="primary full"
                                    disabled={busy || !featured.is_purchasable}
                                    onClick={() => buy(featured)}
                                >
                                    Buy now <ArrowRight size={17} />
                                </button>
                                <button
                                    className="secondary full"
                                    disabled={busy || !featured.is_purchasable}
                                    onClick={() => buy(featured, false)}
                                >
                                    <ShoppingCart size={17} /> Add to cart
                                </button>
                                <span className="stock-dot">
                                    <span className="green-dot" />
                                    {featured.stock_quantity} keys available
                                </span>
                            </>
                        ) : (
                            <p>Currently unavailable</p>
                        )}
                    </div>
                    <div className="checkout-trust">
                        <ShieldCheck size={26} />
                        <b>A little peace of mind</b>
                        <p>
                            Secure sign-in. Transparent offers. Instant
                            simulated key delivery.
                        </p>
                        <div className="payments">
                            <b>VISA</b>
                            <b>PayPal</b>
                            <b>Wallet</b>
                        </div>
                    </div>
                </aside>
            </div>
            <Rail title="You might also like" items={p.related_products} />
        </main>
    );
}
function CartItems({ cart, editable = true }) {
    const { mutate, notify } = useShop();
    const [busy, setBusy] = useState(false);
    const change = async (i, q) => {
        setBusy(true);
        try {
            await mutate(i.offer_id, q);
        } catch (e) {
            notify(e.message);
        } finally {
            setBusy(false);
        }
    };
    return (
        <div className="cart-items" aria-busy={busy}>
            {cart?.items.map((i) => (
                <div
                    className={
                        "cart-line " + (!i.is_purchasable ? "unavailable" : "")
                    }
                    key={i.offer_id}
                >
                    <img src={i.product.image.url} alt="" />
                    <div className="cart-line-details">
                        <Link to={"/products/" + i.product.slug}>
                            {i.product.title}
                        </Link>
                        <small>
                            {i.product.platform?.name} ·{" "}
                            {i.product.region?.name}
                        </small>
                        <small>Sold by {i.seller.name}</small>
                        {!i.is_purchasable && (
                            <span className="error-text">
                                Only {i.available_quantity} available. Adjust
                                this item to continue.
                            </span>
                        )}
                        {editable && (
                            <div className="quantity">
                                <button
                                    aria-label={"Decrease " + i.product.title}
                                    disabled={busy}
                                    onClick={() =>
                                        change(
                                            i,
                                            Math.min(
                                                i.quantity - 1,
                                                i.available_quantity,
                                            ),
                                        )
                                    }
                                >
                                    <Minus size={13} />
                                </button>
                                <span>{i.quantity}</span>
                                <button
                                    aria-label={"Increase " + i.product.title}
                                    disabled={
                                        busy ||
                                        i.quantity >= i.available_quantity
                                    }
                                    onClick={() => change(i, i.quantity + 1)}
                                >
                                    <Plus size={13} />
                                </button>
                            </div>
                        )}
                    </div>
                    <div className="cart-line-price">
                        <b>{money(i.line_total)}</b>
                        {!editable && <small>Qty {i.quantity}</small>}
                        {editable && (
                            <button
                                aria-label={"Remove " + i.product.title}
                                disabled={busy}
                                onClick={() => change(i, 0)}
                            >
                                <Trash2 size={16} />
                            </button>
                        )}
                    </div>
                </div>
            ))}
        </div>
    );
}
function CartPage() {
    const { cart, user } = useShop();
    return (
        <main className="container flow-page">
            <Link className="back-link" to="/catalog">
                <ArrowLeft size={15} /> Keep exploring
            </Link>
            <h1>
                Your cart{" "}
                <span>
                    ({cart?.items.reduce((n, i) => n + i.quantity, 0) || 0})
                </span>
            </h1>
            {!cart ? (
                <Loading />
            ) : cart.items.length ? (
                <div className="flow-layout">
                    <CartItems cart={cart} />
                    <aside className="summary">
                        <h2>Order summary</h2>
                        <div>
                            <span>Subtotal</span>
                            <b>{money(cart.subtotal)}</b>
                        </div>
                        <div>
                            <span>Delivery</span>
                            <span className="accent">Instant · free</span>
                        </div>
                        <div className="total">
                            <b>Total</b>
                            <strong>{money(cart.total)}</strong>
                        </div>
                        {!cart.can_checkout && (
                            <ErrorBox error="Review unavailable items before continuing." />
                        )}
                        <Link
                            className={
                                "primary full " +
                                (!cart.can_checkout ? "disabled" : "")
                            }
                            to={cart.can_checkout ? "/checkout" : "#"}
                        >
                            Continue to checkout <ArrowRight size={17} />
                        </Link>
                        <p className="small muted">
                            <ShieldCheck size={13} />{" "}
                            {user
                                ? "Your cart is saved to your account."
                                : "Sign in or create an account before payment. Your cart will be kept."}
                        </p>
                        <p className="small muted">
                            Demo payment · no real charges
                        </p>
                    </aside>
                </div>
            ) : (
                <div className="empty">
                    <ShoppingCart size={46} />
                    <h2>A great adventure starts with a game.</h2>
                    <p>Your cart is waiting for its first digital key.</p>
                    <Link className="primary" to="/catalog">
                        Explore the store <ArrowRight size={17} />
                    </Link>
                </div>
            )}
        </main>
    );
}
function Auth({ register = false }) {
    const { authenticate, user, ready, notify } = useShop();
    const [busy, setBusy] = useState(false),
        [error, setError] = useState("");
    const navigate = useNavigate();
    const [params] = useSearchParams();
    const claim = params.get("claim");
    const claimState = useLoad(
        claim ? "/checkout/orders/" + claim : "/auth/csrf",
    );
    const next =
        params.get("next")?.startsWith("/") &&
        !params.get("next")?.startsWith("//")
            ? params.get("next")
            : "/account";
    useEffect(() => {
        if (ready && user) navigate(next, { replace: true });
    }, [ready, user]);
    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setError("");
        const form = Object.fromEntries(new FormData(e.currentTarget));
        if (claim) form.claim_order_id = claim;
        try {
            await authenticate(register ? "register" : "login", form);
            notify(
                register ? "Welcome! Your account is ready." : "Welcome back.",
            );
            navigate(next);
        } catch (e) {
            setError(e.message);
            if (e.status === 409)
                notify(
                    "You are signed in. Review your cart before continuing.",
                );
        } finally {
            setBusy(false);
        }
    };
    return (
        <main className="auth-page container">
            <div className="auth-story">
                <span className="eyebrow">WELCOME TO YOUR NEXT ADVENTURE</span>
                <h1>
                    Less searching.
                    <br />
                    More <span>playing.</span>
                </h1>
                <p>
                    A world of digital games, gift cards and good times. All in
                    one place.
                </p>
                <div>
                    <ShieldCheck /> Secure account <KeyRound /> Your keys,
                    always here
                </div>
            </div>
            <section className="auth-card">
                <h2>
                    {register ? "Create your account" : "Good to see you again"}
                </h2>
                <p>
                    {register
                        ? "A few details, a world of possibilities."
                        : "Sign in and pick up where you left off."}
                </p>
                {error && <ErrorBox error={error} />}
                {claim && claimState.error && (
                    <ErrorBox error="This purchase is not available in this browser session. Open it in the browser used to buy." />
                )}
                {claim && (
                    <div className="info-note">
                        <p>
                            Create an account with the purchase email to keep
                            this order in your history. Only this purchase will
                            be linked.
                        </p>
                    </div>
                )}
                <form onSubmit={submit}>
                    {register && (
                        <label>
                            Display name
                            <input
                                required
                                name="display_name"
                                autoComplete="nickname"
                                maxLength={80}
                            />
                        </label>
                    )}
                    <label>
                        Email address
                        <input
                            type="email"
                            required
                            name="email"
                            key={
                                claimState.data?.buyer_email_snapshot || "email"
                            }
                            defaultValue={
                                claimState.data?.buyer_email_snapshot || ""
                            }
                            readOnly={!!claim}
                            autoComplete="email"
                            maxLength={254}
                        />
                    </label>
                    <label>
                        Password
                        <input
                            type="password"
                            required
                            name="password"
                            autoComplete={
                                register ? "new-password" : "current-password"
                            }
                            minLength={register ? 12 : 1}
                            maxLength={128}
                        />
                    </label>
                    {register && (
                        <>
                            <span className="small muted">
                                Use at least 12 characters.
                            </span>
                            <label>
                                Confirm password
                                <input
                                    type="password"
                                    required
                                    name="password_confirmation"
                                    autoComplete="new-password"
                                    minLength={12}
                                    maxLength={128}
                                />
                            </label>
                        </>
                    )}
                    <button
                        disabled={
                            busy ||
                            (!!claim &&
                                (claimState.loading || !!claimState.error))
                        }
                        className="primary full"
                    >
                        {busy ? (
                            <LoaderCircle className="spin" size={18} />
                        ) : register ? (
                            "Create account"
                        ) : (
                            "Sign in"
                        )}
                        <ArrowRight size={17} />
                    </button>
                </form>
                <p className="auth-switch">
                    {register ? "Already have an account?" : "New around here?"}{" "}
                    <Link
                        to={
                            (register ? "/login" : "/register") +
                            "?next=" +
                            encodeURIComponent(next) +
                            (claim ? "&claim=" + encodeURIComponent(claim) : "")
                        }
                    >
                        {register ? "Sign in" : "Create an account"}
                    </Link>
                </p>
            </section>
        </main>
    );
}
function RequireUser({ children }) {
    const { ready, user } = useShop();
    const navigate = useNavigate();
    const location = useLocation();
    useEffect(() => {
        if (ready && !user)
            navigate("/login?next=" + encodeURIComponent(location.pathname), {
                replace: true,
            });
    }, [ready, user]);
    return ready && user ? children : <Loading />;
}
function CheckoutPage() {
    const { cart, currency, refresh, user, ready, mergeGuest } = useShop();
    const cartFingerprint = JSON.stringify(
        cart?.items.map((i) => ({
            offer_id: i.offer_id,
            quantity: i.quantity,
        })),
    );
    const [uncertain, setUncertain] = useState(false);
    const [quote, setQuote] = useState(null),
        [method, setMethod] = useState("card"),
        [fail, setFail] = useState(false),
        [busy, setBusy] = useState(false),
        [error, setError] = useState("");
    const attempt = useRef(null);
    const navigate = useNavigate();
    useEffect(() => {
        if (!uncertain) {
            setQuote(null);
            attempt.current = null;
        }
    }, [cart?.revision, currency, cartFingerprint, user?.id]);
    const review = async () => {
        setBusy(true);
        setError("");
        try {
            await mergeGuest();
            const fresh = await refresh();
            const r = await api("/checkout/quote", {
                method: "POST",
                body: { currency, expected_revision: fresh.revision },
            });
            setQuote(r.data);
            attempt.current = null;
        } catch (e) {
            setError(e.message);
        } finally {
            setBusy(false);
        }
    };
    const pay = async () => {
        setBusy(true);
        setError("");
        const body =
            uncertain && attempt.current
                ? JSON.parse(attempt.current.signature)
                : {
                      quote_token: quote.quote_token,
                      payment_method: method,
                      simulate_failure: fail,
                  };
        const signature = JSON.stringify(body);
        if (!attempt.current || attempt.current.signature !== signature)
            attempt.current = { signature, key: crypto.randomUUID() };
        try {
            setUncertain(true);
            const r = await api("/checkout/attempts", {
                method: "POST",
                headers: { "Idempotency-Key": attempt.current.key },
                body,
            });
            setUncertain(false);
            if (r.data.status === "succeeded") {
                await refresh();
                navigate("/orders/" + r.data.order_id + "?confirmed=1");
            } else {
                setError(r.data.message);
                attempt.current = null;
            }
        } catch (e) {
            setError(e.message);
            if (e.status && e.status < 500) setUncertain(false);
            if (e.status === 409) {
                setQuote(null);
                await refresh();
                attempt.current = null;
            }
        } finally {
            setBusy(false);
        }
    };
    if (!ready || !cart) return <Loading />;
    return (
        <main className="container flow-page">
            <Link className="back-link" to="/cart">
                <ArrowLeft size={15} /> Back to cart
            </Link>
            <span className="eyebrow">
                ONE LAST STEP TO YOUR NEXT ADVENTURE
            </span>
            <h1>Checkout</h1>
            <div className="checkout-steps">
                <span className="done">
                    <Check size={14} /> Cart
                </span>
                <i />
                <span className="current">2 · Review & payment</span>
                <i />
                <span>3 · Your keys</span>
            </div>
            {error && <ErrorBox error={error} />}
            <div className="flow-layout">
                <div>
                    <section className="checkout-section guest-contact">
                        <h2>Where should we send your keys?</h2>
                        <p>
                            Delivery email: <strong>{user.email}</strong>
                        </p>
                        <p className="small muted">
                            Your keys appear here after payment and are emailed
                            to your account address.
                        </p>
                    </section>
                    <div className="checkout-section">
                        <h2>Review your items</h2>
                        <CartItems cart={cart} editable={false} />
                    </div>
                    <section className="checkout-section">
                        <h2>Choose a payment method</h2>
                        <p className="muted small">
                            This is a demonstration. No card details or real
                            payments are collected.
                        </p>
                        <div className="payment-options">
                            {[
                                [
                                    "card",
                                    "Credit / debit card",
                                    "VISA · Mastercard",
                                ],
                                ["paypal", "PayPal", "Fast and familiar"],
                                [
                                    "digital_wallet",
                                    "Digital wallet",
                                    "Your everyday wallet",
                                ],
                            ].map(([code, title, sub]) => (
                                <label
                                    className={
                                        method === code ? "selected" : ""
                                    }
                                    key={code}
                                >
                                    <input
                                        type="radio"
                                        name="payment"
                                        value={code}
                                        checked={method === code}
                                        disabled={busy || uncertain}
                                        onChange={() => {
                                            setMethod(code);
                                            attempt.current = null;
                                        }}
                                    />
                                    <span>
                                        <b>{title}</b>
                                        <small>{sub}</small>
                                    </span>
                                    <ShieldCheck size={20} />
                                </label>
                            ))}
                        </div>
                        <details className="simulation">
                            <summary>Demo payment controls</summary>
                            <label className="check-label">
                                <input
                                    type="checkbox"
                                    checked={fail}
                                    disabled={busy || uncertain}
                                    onChange={(e) => {
                                        setFail(e.target.checked);
                                        attempt.current = null;
                                    }}
                                />{" "}
                                Simulate a declined payment
                            </label>
                        </details>
                    </section>
                </div>
                <aside className="summary">
                    <h2>Order summary</h2>
                    <div>
                        <span>
                            {cart.items.reduce((s, i) => s + i.quantity, 0)}{" "}
                            digital keys
                        </span>
                        <b>{money(cart.subtotal)}</b>
                    </div>
                    <div>
                        <span>Extra fees</span>
                        <span>None</span>
                    </div>
                    <div className="total">
                        <b>Total</b>
                        <strong>{money(cart.total)}</strong>
                    </div>
                    {quote ? (
                        <>
                            <p className="small accent">
                                <CheckCircle2 size={14} /> Price confirmed.
                                Quote valid for 10 minutes.
                            </p>
                            <button
                                className="primary full"
                                disabled={busy}
                                onClick={pay}
                            >
                                {busy
                                    ? "Processing…"
                                    : uncertain
                                      ? "Check payment result"
                                      : "Pay " + money(quote.total)}
                                <ArrowRight size={17} />
                            </button>
                        </>
                    ) : (
                        <button
                            className="primary full"
                            disabled={busy || !cart.can_checkout}
                            onClick={review}
                        >
                            {busy
                                ? "Checking your cart…"
                                : "Confirm price & review"}
                            <ArrowRight size={17} />
                        </button>
                    )}
                    {!cart.can_checkout && (
                        <p className="error-text">
                            Your cart is empty or has unavailable items.{" "}
                            <Link to="/cart">Review cart</Link>
                        </p>
                    )}
                    <p className="small muted">
                        Payment is simulated. Keys are delivered immediately to
                        your order page.
                    </p>
                    <p className="small muted">
                        <ShieldCheck size={13} /> Protected checkout
                    </p>
                </aside>
            </div>
        </main>
    );
}
function Orders() {
    const [params, setParams] = useSearchParams();
    const state = useLoad("/orders?page=" + (params.get("page") || 1));
    return (
        <main className="container flow-page">
            <span className="eyebrow">YOUR COLLECTION STARTS HERE</span>
            <h1>Your orders</h1>
            {state.loading ? (
                <Loading />
            ) : state.error ? (
                <ErrorBox error={state.error} retry={state.retry} />
            ) : state.data.length ? (
                <>
                    <div className="order-list">
                        {state.data.map((o) => (
                            <Link key={o.id} to={"/orders/" + o.id}>
                                <span className="order-icon">
                                    <KeyRound />
                                </span>
                                <div>
                                    <b>{o.order_number}</b>
                                    <small>
                                        {new Date(
                                            o.purchased_at,
                                        ).toLocaleDateString("en-GB", {
                                            dateStyle: "long",
                                        })}
                                    </small>
                                </div>
                                <span className="pill completed">
                                    <Check size={12} /> Completed
                                </span>
                                <strong>{money(o.total)}</strong>
                                <ChevronRight size={18} />
                            </Link>
                        ))}
                    </div>
                    <nav className="pagination">
                        {Array.from(
                            { length: state.meta.last_page },
                            (_, i) => i + 1,
                        ).map((n) => (
                            <button
                                className={
                                    n === state.meta.page ? "active" : ""
                                }
                                key={n}
                                onClick={() => setParams({ page: String(n) })}
                            >
                                {n}
                            </button>
                        ))}
                    </nav>
                </>
            ) : (
                <div className="empty">
                    <KeyRound size={42} />
                    <h2>Your next chapter is waiting.</h2>
                    <p>Purchased keys will appear here.</p>
                    <Link className="primary" to="/catalog">
                        Explore games
                    </Link>
                </div>
            )}
        </main>
    );
}
function Order() {
    const { notify, user } = useShop();
    const [sending, setSending] = useState(false);
    const location = useLocation();
    const id = location.pathname.split("/").pop();
    const state = useLoad("/checkout/orders/" + id);
    const confirmed = new URLSearchParams(location.search).has("confirmed");
    const copy = async (value) => {
        try {
            await navigator.clipboard.writeText(value);
            notify("Key copied to clipboard");
        } catch {
            notify("Select the key and copy it manually.");
        }
    };
    if (state.loading) return <Loading />;
    if (state.error)
        return (
            <main className="container flow-page">
                <ErrorBox error={state.error} retry={state.retry} />
            </main>
        );
    const o = state.data;
    const emailAgain = async () => {
        setSending(true);
        try {
            await api("/checkout/orders/" + id + "/email", { method: "POST" });
            state.retry();
        } catch (e) {
            notify(e.message);
        } finally {
            setSending(false);
        }
    };
    const claimCurrent = async () => {
        setSending(true);
        try {
            await api("/checkout/orders/" + id + "/claim", { method: "POST" });
            state.retry();
            notify("Order saved to your account");
        } catch (e) {
            notify(e.message);
        } finally {
            setSending(false);
        }
    };
    return (
        <main className="container flow-page order-detail">
            {confirmed && (
                <div className="confirmation">
                    <CheckCircle2 size={45} />
                    <span className="eyebrow">ALL SET. TIME TO PLAY.</span>
                    <h1>Your next adventure is ready.</h1>
                    <p>
                        Payment completed. Your simulated digital keys are right
                        here.
                    </p>
                </div>
            )}
            <section
                className="checkout-section delivery-status"
                aria-live="polite"
            >
                <h2>Your keys are ready</h2>
                <p>
                    Delivery email: <strong>{o.buyer_email_snapshot}</strong>
                </p>
                <p>
                    {{
                        sent: "Your key email was accepted by the email provider. Check your inbox and spam folder.",
                        sending: "Your key email is being sent.",
                        failed: "Your purchase is complete, but the email could not be sent. Your keys are available below.",
                        unconfigured:
                            "Your purchase is complete. Email delivery is not configured for this demo yet; copy your keys below.",
                        pending:
                            "Your purchase is complete. Your key email is waiting to be sent.",
                    }[o.email_delivery.status] ||
                        "Your keys are available below."}
                </p>
                {o.email_delivery.can_retry && (
                    <button
                        className="secondary"
                        disabled={sending}
                        onClick={emailAgain}
                    >
                        {sending ? "Sending…" : "Retry key email"}
                    </button>
                )}
            </section>
            {o.is_guest_order && (
                <section className="checkout-section guest-registration">
                    <h2>Keep your games in one place</h2>
                    <p>
                        Your purchase is complete—creating an account is
                        optional. Link this order so you can retrieve it on any
                        device.
                    </p>
                    {!user ? (
                        <div className="button-row">
                            <Link
                                className="primary"
                                to={
                                    "/register?next=" +
                                    encodeURIComponent("/orders/" + id) +
                                    "&claim=" +
                                    id
                                }
                            >
                                Create account & save order
                            </Link>
                            <Link
                                className="secondary"
                                to={
                                    "/login?next=" +
                                    encodeURIComponent("/orders/" + id) +
                                    "&claim=" +
                                    id
                                }
                            >
                                Sign in & save order
                            </Link>
                        </div>
                    ) : user.email === o.buyer_email_snapshot ? (
                        <button
                            className="primary"
                            disabled={sending}
                            onClick={claimCurrent}
                        >
                            Save this order to my account
                        </button>
                    ) : (
                        <p>
                            To save this purchase, sign in with its delivery
                            email address.
                        </p>
                    )}

                    <p className="small muted">
                        Until linked, this page is available in the purchasing
                        browser session. Keep your email or copy your keys.
                    </p>
                </section>
            )}
            <div className="page-heading">
                <div>
                    <Link className="back-link" to="/orders">
                        <ArrowLeft size={14} /> All orders
                    </Link>
                    <h2>Order {o.order_number}</h2>
                    <p>
                        {new Date(o.purchased_at).toLocaleDateString("en-GB", {
                            dateStyle: "long",
                        })}{" "}
                        · {money(o.total)}
                    </p>
                </div>
                <span className="pill completed">
                    <Check size={12} /> Completed
                </span>
            </div>
            {o.items.map((i) => (
                <section className="key-card" key={i.id}>
                    <div>
                        <img src={i.product.image.url} alt="" />
                        <span>
                            <h3>{i.product.title}</h3>
                            <p>
                                {i.product.platform?.name} ·{" "}
                                {i.product.region?.name}
                            </p>
                            <small>
                                Sold by {i.seller} · Qty {i.quantity}
                            </small>
                        </span>
                        <b>{money(i.line_total)}</b>
                    </div>
                    {i.keys.map((k) => (
                        <div className="digital-key" key={k.unit_number}>
                            <KeyRound size={18} />
                            <code>{k.value}</code>
                            <button
                                className="secondary"
                                onClick={() => copy(k.value)}
                                aria-label={"Copy key " + k.unit_number}
                            >
                                <Copy size={15} /> Copy
                            </button>
                        </div>
                    ))}
                </section>
            ))}
            <div className="info-note">
                <ShieldCheck />
                <p>
                    These are simulated digital keys for an internship
                    demonstration. They cannot be redeemed.{" "}
                    {o.is_guest_order
                        ? "Keep your email, or create an account to save this historical order in your history."
                        : "Your keys are saved in your account order history and keys library."}
                </p>
            </div>
        </main>
    );
}
function Account() {
    const location = useLocation();
    const { user, setUser, logout, notify } = useShop();
    const [error, setError] = useState(""),
        [busy, setBusy] = useState(false);
    const navigate = useNavigate();
    const update = async (e, password = false) => {
        e.preventDefault();
        setBusy(true);
        setError("");
        try {
            const r = await api(password ? "/me/password" : "/me", {
                method: password ? "POST" : "PATCH",
                body: Object.fromEntries(new FormData(e.currentTarget)),
            });
            if (password) {
                window.dispatchEvent(new Event("session-expired"));
                csrf = "";
                notify("Password changed. Sign in again on all devices.");
                navigate("/login");
            } else {
                setUser(r.data);
                notify("Profile updated");
            }
        } catch (e) {
            setError(e.message);
        } finally {
            setBusy(false);
        }
    };
    return (
        <main className="container flow-page account-page">
            <h1>
                {location.pathname.endsWith("password")
                    ? "Change password"
                    : "My personal info"}
            </h1>
            {error && <ErrorBox error={error} />}
            <div className="account-layout">
                <div>
                    {!location.pathname.endsWith("password") && (
                        <section className="checkout-section">
                            <h2>Your profile</h2>
                            <form onSubmit={update}>
                                <label>
                                    Email address
                                    <input value={user.email} readOnly />
                                </label>
                                <label>
                                    Display name
                                    <input
                                        name="display_name"
                                        defaultValue={user.display_name}
                                        required
                                        maxLength={80}
                                    />
                                </label>
                                <button className="primary" disabled={busy}>
                                    Save changes
                                </button>
                            </form>
                        </section>
                    )}
                    {location.pathname.endsWith("password") && (
                        <section className="checkout-section">
                            <h2>Change password</h2>
                            <p className="muted small">
                                Changing your password signs you out on all
                                devices.
                            </p>
                            <form onSubmit={(e) => update(e, true)}>
                                <label>
                                    Current password
                                    <input
                                        name="current_password"
                                        type="password"
                                        required
                                        autoComplete="current-password"
                                    />
                                </label>
                                <label>
                                    New password
                                    <input
                                        name="password"
                                        type="password"
                                        minLength={12}
                                        maxLength={128}
                                        required
                                        autoComplete="new-password"
                                    />
                                </label>
                                <label>
                                    Confirm new password
                                    <input
                                        name="password_confirmation"
                                        type="password"
                                        minLength={12}
                                        maxLength={128}
                                        required
                                        autoComplete="new-password"
                                    />
                                </label>
                                <button className="secondary" disabled={busy}>
                                    Update password
                                </button>
                            </form>
                        </section>
                    )}
                </div>
                <Link className="account-orders" to="/orders">
                    <KeyRound size={35} />
                    <h2>Your orders & keys</h2>
                    <p>Every adventure, all in one place.</p>
                    <span>
                        View order history <ArrowRight size={17} />
                    </span>
                </Link>
            </div>
        </main>
    );
}
function Avatar({ name = "Buyer" }) {
    return (
        <span className="buyer-avatar" aria-hidden="true">
            <Gamepad2 size={23} />
            <small>{name.charAt(0).toUpperCase()}</small>
        </span>
    );
}
function ProfileMenu() {
    const { user, logout, notify } = useShop();
    const [open, setOpen] = useState(false);
    const ref = useRef<HTMLDivElement>(null);
    const location = useLocation();
    const navigate = useNavigate();
    useEffect(() => setOpen(false), [location.pathname]);
    useEffect(() => {
        const close = (e: PointerEvent) => {
            if (!ref.current?.contains(e.target as Node)) setOpen(false);
        };
        document.addEventListener("pointerdown", close);
        return () => document.removeEventListener("pointerdown", close);
    }, []);
    if (!user)
        return (
            <Link to="/login" aria-label="Sign in">
                <UserRound size={23} />
                <span>Sign in</span>
            </Link>
        );
    const rows = [
        [Heart, "Favorites", "/preview/favorites"],
        [UserRound, "My account", "/account"],
        [Wallet, "Balance", "/account/preview/balance"],
        [Gem, "Enebium", "/account/preview/enebium"],
        [Gift, "Cashback", "/account/preview/cashback"],
        [ShoppingCart, "Orders", "/orders"],
        [Gamepad2, "Keys library", "/library"],
    ] as const;
    return (
        <div
            className="profile-control"
            ref={ref}
            onBlur={(e) => {
                if (!e.currentTarget.contains(e.relatedTarget as Node))
                    setOpen(false);
            }}
            onKeyDown={(e) => {
                if (e.key === "Escape") {
                    setOpen(false);
                    ref.current
                        ?.querySelector<HTMLButtonElement>("button")
                        ?.focus();
                }
            }}
        >
            <button
                className="profile-trigger"
                aria-label="Your account"
                aria-expanded={open}
                aria-controls="buyer-menu"
                onClick={() => setOpen(!open)}
            >
                <Avatar name={user.display_name} />
            </button>
            {open && (
                <nav
                    id="buyer-menu"
                    className="profile-dropdown"
                    aria-label="Your account menu"
                >
                    <Link className="profile-summary" to="/account">
                        <Avatar name={user.display_name} />
                        <span>
                            <strong>{user.display_name}</strong>
                            <small>{user.email}</small>
                        </span>
                    </Link>
                    {rows.map(([Icon, label, href]) => (
                        <Link key={label} to={href}>
                            <Icon size={21} />
                            <span>{label}</span>
                            {["Balance", "Enebium", "Cashback"].includes(
                                label,
                            ) && <small className="preview-tag">Demo</small>}
                        </Link>
                    ))}
                    <button
                        onClick={async () => {
                            try {
                                await logout();
                                setOpen(false);
                                navigate("/");
                            } catch (e) {
                                notify(e.message);
                            }
                        }}
                    >
                        <LogOut size={21} />
                        Logout
                    </button>
                </nav>
            )}
        </div>
    );
}
function AccountHeader() {
    const { user } = useShop();
    return (
        <header className="account-header">
            <Logo />
            <GlobalSearch />
            <div className="account-header-actions">
                {user && <span className="account-email">{user.email}</span>}
                <ProfileMenu />
            </div>
        </header>
    );
}
function AccountShell({ children }) {
    const location = useLocation();
    const items = [
        [UserRound, "Overview", "/account"],
        [Wallet, "Balance", "/account/preview/balance"],
        [ShoppingCart, "Orders", "/orders"],
        [Gamepad2, "Keys library", "/library"],
        [Headphones, "Tickets", "/account/preview/tickets"],
    ] as const;
    return (
        <div className="account-shell">
            <aside className="account-sidebar">
                <nav aria-label="Account navigation">
                    <span className="sidebar-title">My account</span>
                    {items.map(([Icon, label, href]) => (
                        <Link
                            key={href}
                            to={href}
                            aria-current={
                                location.pathname === href ? "page" : undefined
                            }
                        >
                            <Icon size={25} />
                            {label}
                            {["Balance", "Tickets"].includes(label) && (
                                <small>Demo</small>
                            )}
                        </Link>
                    ))}
                    <details
                        open={
                            location.pathname === "/account/profile" ||
                            location.pathname === "/account/password"
                        }
                    >
                        <summary>
                            <SettingsIcon size={25} />
                            Settings
                            <ChevronDown size={16} />
                        </summary>
                        <Link
                            to="/account/profile"
                            aria-current={
                                location.pathname === "/account/profile"
                                    ? "page"
                                    : undefined
                            }
                        >
                            My personal info
                        </Link>
                        <Link
                            to="/account/password"
                            aria-current={
                                location.pathname === "/account/password"
                                    ? "page"
                                    : undefined
                            }
                        >
                            Change password
                        </Link>
                    </details>
                </nav>
                <Link className="back-store" to="/">
                    <ArrowLeft size={18} />
                    Back to store
                </Link>
            </aside>
            <div className="account-content">
                <div className="account-breadcrumb">
                    <Link to="/account">My account</Link>
                    <span>·</span>
                    <span>
                        {location.pathname === "/account"
                            ? "Overview"
                            : location.pathname === "/library"
                              ? "Keys library"
                              : location.pathname.split("/").pop()}
                    </span>
                </div>
                {children}
            </div>
        </div>
    );
}
function AccountOverview() {
    const { user } = useShop();
    const state = useLoad("/orders");
    const latest = (state.data || [])
        .flatMap((o) => (o.products || []).map((p) => ({ ...p, order: o.id })))
        .slice(0, 3);
    return (
        <main className="account-overview">
            <h1>Overview</h1>
            <div className="overview-grid">
                <div>
                    <section className="dashboard-card">
                        <h2>Profile</h2>
                        <div className="dashboard-profile">
                            <Avatar name={user.display_name} />
                            <div>
                                <strong>{user.display_name}</strong>
                                <p>{user.email}</p>
                            </div>
                            <Link
                                to="/account/profile"
                                aria-label="Edit profile"
                            >
                                <Pencil size={20} />
                            </Link>
                        </div>
                    </section>
                    <section className="dashboard-card balance-preview">
                        <div className="balance-top">
                            <div>
                                <h2>
                                    Total balance{" "}
                                    <span className="preview-tag">
                                        Demo preview
                                    </span>
                                </h2>
                                <strong>€0.00</strong>
                            </div>
                            <Link
                                className="secondary"
                                to="/account/preview/balance"
                            >
                                Wallet overview
                            </Link>
                        </div>
                        <p>
                            Wallet and balance transactions are not available in
                            this demo.
                        </p>
                        <div className="balance-line">
                            <div>
                                <h3>Gift card balance</h3>
                                <p>Visual preview only.</p>
                            </div>
                            <span>€0.00</span>
                        </div>
                        <div className="balance-line">
                            <div>
                                <h3>Bonus</h3>
                                <p>No cashback or rewards are accrued.</p>
                            </div>
                            <span>€0.00</span>
                        </div>
                    </section>
                </div>
                <section className="dashboard-card latest-purchases">
                    <h2>Latest purchases</h2>
                    {state.loading ? (
                        <Loading />
                    ) : state.error ? (
                        <ErrorBox error={state.error} retry={state.retry} />
                    ) : latest.length ? (
                        <div className="purchase-covers">
                            {latest.map((p, i) => (
                                <Link
                                    key={i}
                                    to={"/orders/" + p.order}
                                    title={p.title}
                                >
                                    <img src={p.image?.url} alt={p.title} />
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <div className="dashboard-empty">
                            <Gamepad2 size={36} />
                            <p>Your purchases will appear here.</p>
                            <Link to="/catalog">Explore games</Link>
                        </div>
                    )}
                    <Link className="secondary" to="/library">
                        View keys library
                    </Link>
                </section>
            </div>
        </main>
    );
}
function KeysLibrary() {
    const [params, setParams] = useSearchParams();
    const term = params.get("q") || "";
    const [draft, setDraft] = useState(term);
    useEffect(() => setDraft(term), [term]);
    const state = useLoad("/library?" + params.toString());
    return (
        <main className="keys-library">
            <h1>My library</h1>
            <form
                className="library-search"
                role="search"
                onSubmit={(e) => {
                    e.preventDefault();
                    setParams(draft.trim() ? { q: draft.trim() } : {});
                }}
            >
                <Search size={20} />
                <input
                    aria-label="Search your keys library"
                    placeholder="Product name (typos welcome) / order ID"
                    maxLength={120}
                    value={draft}
                    onChange={(e) => setDraft(e.target.value)}
                />
                <button type="submit">Search</button>
            </form>
            {state.loading ? (
                <Loading />
            ) : state.error ? (
                <ErrorBox error={state.error} retry={state.retry} />
            ) : state.data?.length ? (
                <>
                    <div
                        className="library-table-wrap"
                        role="region"
                        aria-label="Purchased products"
                        tabIndex={0}
                    >
                        <table className="library-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Date</th>
                                    <th>Order ID</th>
                                    <th>Seller</th>
                                    <th>Price</th>
                                    <th>
                                        <span className="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {state.data.map((i) => (
                                    <tr key={i.id}>
                                        <td>
                                            <div className="library-product">
                                                <img
                                                    src={i.product.image?.url}
                                                    alt=""
                                                />
                                                <span>
                                                    {i.product.title}
                                                    <small>
                                                        {
                                                            i.product.platform
                                                                ?.name
                                                        }{" "}
                                                        ·{" "}
                                                        {i.product.region?.name}{" "}
                                                        · Qty {i.quantity}
                                                    </small>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            {new Date(
                                                i.purchased_at,
                                            ).toLocaleDateString("en-GB")}
                                        </td>
                                        <td>{i.order_number}</td>
                                        <td>{i.seller}</td>
                                        <td>
                                            <strong>{money(i.total)}</strong>
                                        </td>
                                        <td>
                                            <Link to={"/orders/" + i.order_id}>
                                                View keys
                                                <span className="sr-only">
                                                    {" "}
                                                    for {i.product.title}
                                                </span>
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <nav className="pagination" aria-label="Library pages">
                        {Array.from(
                            { length: state.meta.last_page },
                            (_, i) => i + 1,
                        ).map((n) => (
                            <button
                                key={n}
                                aria-current={
                                    n === state.meta.page ? "page" : undefined
                                }
                                className={
                                    n === state.meta.page ? "active" : ""
                                }
                                onClick={() => {
                                    const next = new URLSearchParams(params);
                                    next.set("page", String(n));
                                    setParams(next);
                                }}
                            >
                                {n}
                            </button>
                        ))}
                    </nav>
                </>
            ) : (
                <div className="dashboard-card dashboard-empty">
                    <KeyRound size={38} />
                    <h2>
                        {term
                            ? "No matching purchases"
                            : "Your library is waiting"}
                    </h2>
                    <p>
                        {term
                            ? "Try another product name or order number."
                            : "Purchased products appear here after checkout."}
                    </p>
                    <Link to={term ? "/library" : "/catalog"}>
                        {term ? "Clear search" : "Explore games"}
                    </Link>
                </div>
            )}
        </main>
    );
}
function DemoPreview() {
    const feature = useLocation().pathname.split("/").pop();
    const labels = {
        balance: "Balance",
        cashback: "Cashback",
        enebium: "Enebium",
        tickets: "Support tickets",
        favorites: "Favorites",
    };
    return (
        <main className="container flow-page demo-preview">
            <span className="preview-tag">Demo preview</span>
            <h1>{labels[feature] || "Feature preview"}</h1>
            <div className="dashboard-card">
                <Gift size={40} />
                <h2>Planned for a future update</h2>
                <p>
                    This feature is shown as a visual preview.{" "}
                    {feature === "balance" || feature === "cashback"
                        ? "No funds, cashback or rewards are stored or transferred."
                        : "It does not save or submit information."}
                </p>
                <Link className="secondary" to="/">
                    Back to store
                </Link>
            </div>
        </main>
    );
}
function DiscoveryStrip({ links }) {
    return (
        <nav
            className="container discovery-strip"
            aria-label="Browse platforms and categories"
        >
            {links.map((link) => {
                const Icon =
                    { gamepad: Gamepad2, gift: Gift, layers: Layers }[
                        link.icon
                    ] || Gamepad2;
                return (
                    <Link key={link.url} to={link.url}>
                        <Icon size={30} />
                        <span>{link.title}</span>
                    </Link>
                );
            })}
        </nav>
    );
}
function GenreTiles({ block }) {
    return (
        <section className="genre-section">
            <div className="container">
                <h2>{block.title}</h2>
                <div className="genre-tiles">
                    {block.items.map((g) => (
                        <Link key={g.code} to={g.url}>
                            <small>{g.count} products</small>
                            <Gamepad2 size={32} />
                            <strong>{g.name}</strong>
                        </Link>
                    ))}
                </div>
                <Link className="secondary" to={block.configuration.cta_url}>
                    {block.configuration.cta_label}
                </Link>
            </div>
        </section>
    );
}

function Scroll() {
    const { pathname } = useLocation();
    useEffect(() => {
        window.scrollTo(0, 0);
        if (!pathname.startsWith("/products/"))
            document.title = "Eneba · Your next adventure";
    }, [pathname]);
    return null;
}
function App() {
    return (
        <BrowserRouter>
            <Provider>
                <Scroll />
                <Header />
                <Routes>
                    <Route path="/" element={<Home />} />
                    <Route path="/catalog" element={<Catalog />} />
                    <Route path="/products/:slug" element={<Product />} />
                    <Route path="/cart" element={<CartPage />} />
                    <Route path="/login" element={<Auth />} />
                    <Route path="/register" element={<Auth register />} />
                    <Route
                        path="/checkout"
                        element={
                            <RequireUser>
                                <CheckoutPage />
                            </RequireUser>
                        }
                    />
                    <Route
                        path="/account"
                        element={
                            <RequireUser>
                                <AccountShell>
                                    <AccountOverview />
                                </AccountShell>
                            </RequireUser>
                        }
                    />
                    <Route
                        path="/orders"
                        element={
                            <RequireUser>
                                <AccountShell>
                                    <Orders />
                                </AccountShell>
                            </RequireUser>
                        }
                    />
                    <Route
                        path="/account/profile"
                        element={
                            <RequireUser>
                                <AccountShell>
                                    <Account />
                                </AccountShell>
                            </RequireUser>
                        }
                    />
                    <Route
                        path="/account/password"
                        element={
                            <RequireUser>
                                <AccountShell>
                                    <Account />
                                </AccountShell>
                            </RequireUser>
                        }
                    />
                    <Route
                        path="/library"
                        element={
                            <RequireUser>
                                <AccountShell>
                                    <KeysLibrary />
                                </AccountShell>
                            </RequireUser>
                        }
                    />
                    <Route path="/preview/:feature" element={<DemoPreview />} />
                    <Route
                        path="/account/preview/:feature"
                        element={
                            <RequireUser>
                                <AccountShell>
                                    <DemoPreview />
                                </AccountShell>
                            </RequireUser>
                        }
                    />
                    <Route path="/orders/:id" element={<Order />} />
                    <Route
                        path="*"
                        element={
                            <main className="empty">
                                <h1>This page wandered off.</h1>
                                <Link to="/" className="primary">
                                    Back to the store
                                </Link>
                            </main>
                        }
                    />
                </Routes>
                <Footer />
            </Provider>
        </BrowserRouter>
    );
}
createRoot(document.getElementById("root")).render(<App />);

export type Currency = "EUR" | "USD" | "GBP";
export type Money = { currency: Currency; minor: string };
export type Buyer = { id: string; email: string; display_name: string };
export type ProductCard = {
    id: string;
    slug: string;
    title: string;
    type: "game" | "gift_card" | "subscription";
    image: { url: string; alt: string };
    platform: { code: string; name: string };
    region: { code: string; name: string };
    starting_price: Money | null;
    availability: "available" | "sold_out";
    catalog_added_at: string;
    demo_cashback: { percent: string; is_mock: true };
};
export type GuestItem = { offer_id: string; quantity: number };
export type CartLine = {
    offer_id: string;
    product: ProductCard;
    seller: { id: string; name: string };
    quantity: number;
    available_quantity: number;
    unit_price: Money;
    line_total: Money;
    is_purchasable: boolean;
};
export type Cart = {
    id: string | null;
    revision: string | null;
    currency: Currency;
    fx_version: string;
    items: CartLine[];
    issues: unknown[];
    can_checkout: boolean;
    subtotal: Money | null;
    total: Money | null;
};

import { test, expect } from "@playwright/test";

test("guest cart, registration, decline, purchase, sign-in and key retrieval", async ({
    page,
}) => {
    await page.goto("/");
    await page
        .getByRole("link", { name: /Elden Ring.*CASHBACK/ })
        .first()
        .click();
    await page
        .getByRole("button", { name: "Add to cart", exact: true })
        .click();
    await page
        .getByRole("link", { name: "Shopping cart, 1 items", exact: true })
        .click();
    await page.reload();
    await expect(
        page.getByRole("heading", { name: "Your cart (1)", exact: true }),
    ).toBeVisible();
    await page
        .getByRole("link", { name: "Continue to checkout", exact: true })
        .click();
    await expect(
        page.getByRole("heading", {
            name: "Good to see you again",
            exact: true,
        }),
    ).toBeVisible();
    await page
        .getByRole("link", { name: "Create an account", exact: true })
        .click();
    const email = `browser-${Date.now()}@example.com`,
        password = "Browser-demo-password-2026";
    await page
        .getByRole("textbox", { name: "Display name", exact: true })
        .fill("Browser test");
    await page
        .getByRole("textbox", { name: "Email address", exact: true })
        .fill(email);
    await page
        .getByRole("textbox", { name: "Password", exact: true })
        .fill(password);
    await page
        .getByRole("textbox", { name: "Confirm password", exact: true })
        .fill(password);
    await page
        .getByRole("button", { name: "Create account", exact: true })
        .click();
    await expect(
        page.getByRole("heading", { name: "Checkout", exact: true }),
    ).toBeVisible();
    await page
        .getByRole("button", { name: "Confirm price & review", exact: true })
        .click();
    await page.getByText("Demo payment controls", { exact: true }).click();
    await page
        .getByRole("checkbox", {
            name: "Simulate a declined payment",
            exact: true,
        })
        .check();
    await page.getByRole("button", { name: /^Pay €/ }).click();
    await expect(page.getByRole("alert")).toContainText(
        "Demo payment declined",
    );
    await page
        .getByRole("checkbox", {
            name: "Simulate a declined payment",
            exact: true,
        })
        .uncheck();
    await page
        .getByRole("radio", { name: "PayPal Fast and familiar", exact: true })
        .check();
    await page.getByRole("button", { name: /^Pay €/ }).click();
    await expect(
        page.getByRole("heading", {
            name: "Your next adventure is ready.",
            exact: true,
        }),
    ).toBeVisible();
    const orderUrl = page.url(),
        key = await page.locator("code").innerText();
    expect(key).toMatch(/^DEMO-[A-F0-9-]+$/);
    await page
        .getByRole("button", { name: "Your account", exact: true })
        .click();
    await page.getByRole("button", { name: "Logout", exact: true }).click();
    await page.goto(orderUrl);
    await expect(page.getByRole("alert")).toContainText("Order not found");
    await page.getByRole("link", { name: "Sign in", exact: true }).click();
    await page
        .getByRole("textbox", { name: "Email address", exact: true })
        .fill(email);
    await page
        .getByRole("textbox", { name: "Password", exact: true })
        .fill(password);
    await page.getByRole("button", { name: "Sign in", exact: true }).click();
    await expect(
        page.getByRole("button", { name: "Your account", exact: true }),
    ).toBeVisible();
    await page.goto(orderUrl);
    await expect(page.locator("code")).toHaveText(key);
});

test("mobile catalogue remains usable without page overflow", async ({
    page,
}) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto("/catalog");
    await page.getByRole("button", { name: "Filters", exact: true }).click();
    const giftCards = page.getByRole("checkbox", {
        name: "Gaming gift cards",
        exact: true,
    });
    await giftCards.click();
    await expect(giftCards).toBeChecked();
    await expect(page.getByText("15 results", { exact: true })).toBeVisible();
    expect(
        await page.evaluate(
            () => document.documentElement.scrollWidth <= window.innerWidth,
        ),
    ).toBe(true);
});

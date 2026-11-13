import { test, expect } from "@playwright/test";

test("payment requires sign-in even when checkout is opened directly", async ({
    page,
}) => {
    await page.goto("/checkout");
    await expect(page).toHaveURL(/\/login\?next=%2Fcheckout/);
    await expect(
        page.getByRole("button", { name: "Sign in", exact: true }),
    ).toBeVisible();
    await expect(
        page.getByRole("heading", { name: "Checkout", exact: true }),
    ).toHaveCount(0);
});

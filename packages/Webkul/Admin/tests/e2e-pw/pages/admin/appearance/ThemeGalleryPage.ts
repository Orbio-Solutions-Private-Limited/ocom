import { expect, type Page } from "@playwright/test";
import { BasePage } from "../../BasePage";

export class ThemeGalleryPage extends BasePage {
    constructor(page: Page) {
        super(page);
    }

    private group(heading: string) {
        return this.page
            .getByText(heading, { exact: true })
            .locator("../..");
    }

    async open(): Promise<void> {
        await this.visit("admin/appearance/themes");

        await expect(this.page.getByText("My Themes", { exact: true })).toBeVisible();
    }

    async expectInstalledThemesWithoutMarketplace(): Promise<void> {
        await this.open();

        const installed = this.group("My Themes");

        await expect(installed).toBeVisible();

        await expect(
            this.page.getByText("Buy Themes", { exact: true }),
        ).toHaveCount(0);

        await expect(
            installed.getByRole("link", { name: "Customize" }).first(),
        ).toBeVisible();

        await expect(
            this.page.getByRole("link", { name: "View & Buy" }),
        ).toHaveCount(0);
    }
}

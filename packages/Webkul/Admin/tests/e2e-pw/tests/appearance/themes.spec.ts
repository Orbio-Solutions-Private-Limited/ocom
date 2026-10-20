import { test } from "../../setup";
import { ThemeGalleryPage } from "../../pages/admin/appearance/ThemeGalleryPage";

test.describe("theme gallery", () => {
    test("should list installed themes without a marketplace", async ({
        adminPage,
    }) => {
        const themeGalleryPage = new ThemeGalleryPage(adminPage);

        await themeGalleryPage.expectInstalledThemesWithoutMarketplace();
    });
});

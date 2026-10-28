<?php

namespace Webkul\Theme;

use Illuminate\Foundation\Vite;

class Theme
{
    /**
     * Contains theme parent.
     *
     * @var Theme
     */
    public $parent;

    /**
     * Create a new theme instance.
     *
     * @param  string  $code
     * @param  string  $name
     * @param  string  $assetsPath
     * @param  string  $viewsPath
     * @return void
     */
    public function __construct(
        public $code,
        public $name = null,
        public $assetsPath = null,
        public $viewsPath = null,
        public $viewsNamespace = null,
        public $vite = []
    ) {
        $this->assetsPath = $assetsPath === null ? $code : $assetsPath;

        $this->viewsPath = $viewsPath === null ? $code : $viewsPath;
    }

    /**
     * Sets the parent.
     *
     * @param  Theme
     * @return void
     */
    public function setParent(Theme $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Return the parent.
     *
     * @return Theme
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return all the possible view paths.
     *
     * @return array
     */
    public function getViewPaths()
    {
        $paths = [];

        $theme = $this;

        do {
            if (substr($theme->viewsPath, 0, 1) === DIRECTORY_SEPARATOR) {
                $path = base_path(substr($theme->viewsPath, 1));
            } else {
                $path = $theme->viewsPath;
            }

            if (! in_array($path, $paths)) {
                $paths[] = $path;
            }
        } while ($theme = $theme->parent);

        return $paths;
    }

    /**
     * Convert to asset url based on current theme.
     *
     * @return string
     */
    public function url(string $url)
    {
        try {
            return $this->viteAsset($url);
        } catch (\Exception $e) {
            if ($fallback = $this->fallbackTheme()) {
                return $fallback->url($url);
            }

            report($e);

            abort(404);
        }
    }

    /**
     * Set bagisto vite.
     *
     * @return Vite
     */
    public function setBagistoVite(array $entryPoints)
    {
        return $this->makeVite()->withEntryPoints($entryPoints);
    }

    /**
     * Resolve the Vite asset url for this theme's build.
     */
    protected function viteAsset(string $url): string
    {
        $viteUrl = trim($this->vite['package_assets_directory'], '/').'/'.$url;

        return $this->makeVite()->asset($viteUrl);
    }

    /**
     * Theme whose Vite build is used when this theme does not ship the asset.
     */
    protected function fallbackTheme(): ?self
    {
        $fallbackCode = config('themes.shop-default');

        if (
            ! $fallbackCode
            || $this->code === $fallbackCode
            || ! themes()->exists($fallbackCode)
        ) {
            return null;
        }

        return themes()->find($fallbackCode);
    }

    /**
     * Vite instance bound to this theme's hot file and build directory.
     */
    protected function makeVite(): Vite
    {
        return (new Vite)
            ->useHotFile($this->vite['hot_file'])
            ->useBuildDirectory($this->vite['build_directory']);
    }
}

<?php
namespace LoveCoding\TwigAsset;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use Symfony\Component\Asset\{
    PackageInterface,
    VersionStrategyInterface,
    Package,
    PathPackage,
    UrlPackage,
    Packages
};
use Symfony\Component\Asset\VersionStrategy\{
    EmptyVersionStrategy,
    StaticVersionStrategy,
    JsonManifestVersionStrategy
};

class TwigAssetManagement
{
    private $currentStrategy;

    private $defaultPackage;
    private $namedPackages = [];

    private $settings = [
        'version' => '',
        'version_format' => '%s?v=%s',
        'json_manifest' => '',
        'public_name' => '',
        'public_path' => '',
        'external_name' => '',
        'external_url' => ''
    ];

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    public function getAssetExtension() : AbstractExtension
    {
        $this->loadDefaultPackage();
        $this->loadNamedPackages();

        return new TwigAssetExtension(new Packages($this->defaultPackage, $this->namedPackages));
    }

    /**
     * Add package to named packages
     *
     * @param string $name
     * @param PackageInterface $package
     * @return void
     */
    public function addPackage(string $name, PackageInterface $package) : void
    {
        $this->namedPackages[$name] = $package;
    }

    public function addPath(string $name, string $path) : void
    {
        $this->addPackage($name, new PathPackage($path, $this->getCurrentStrategy()));
    }

    public function addUrl(string $name, string $path) : void
    {
        $this->addPackage($name, new UrlPackage($path, $this->getCurrentStrategy()));
    }

    /**
     * Add group assets url to url package
     * For each asset, one of the URLs will be randomly used.
     * But, the selection is deterministic, meaning that each asset will be always served by the same domain
     *
     * @param string $name
     * @param array $path
     * @return void
     */
    public function addUrls(string $name, array $path) : void
    {
        $this->addPackage($name, new UrlPackage($path, $this->getCurrentStrategy()));
    }

    /********************************************************************************
     * Setter and getter
     *******************************************************************************/

    public function setCurrentStrategy(VersionStrategyInterface $strategy) 
    {
        $this->currentStrategy = $strategy;
    }

    public function getCurrentStrategy()
    {
        if ( $this->currentStrategy instanceof VersionStrategyInterface ) {
            return $this->currentStrategy;
        }

        $this->loadDefaultStrategy();

        return $this->currentStrategy;
    }

    /********************************************************************************
     * Loader
     *******************************************************************************/
    private function loadDefaultStrategy()
    {
        // user has not been custom strategy via method `setCurrentStrategy`
        // load default
        $version = $this->settings['version'];
        $versionFormat = $this->settings['version_format'];
        $jsonManifest = $this->settings['json_manifest'];

        // higher priority
        if ($jsonManifest !== '' && is_string($jsonManifest))
        {
            $this->currentStrategy = new JsonManifestVersionStrategy($jsonManifest);
        }
        // normal priority
        else if ($version !== '' && is_string($version))
        {
            $this->currentStrategy = new StaticVersionStrategy($version, $versionFormat);
        }
        // lower priority
        else
        {
            $this->currentStrategy = new EmptyVersionStrategy();
        }
    }

    private function loadDefaultPackage()
    {
        $this->defaultPackage = new Package($this->getCurrentStrategy());
    }

    private function loadNamedPackages()
    {
        $externalName = $this->settings['external_name'];
        $externalUrl = $this->settings['external_url'];
        $publicName = $this->settings['public_name'];
        $publicPath = $this->settings['public_path'];

        if (is_string($publicName) && $publicPath !== '' && is_string($publicPath))
        {
            $this->addPackage($publicName, new PathPackage($publicPath, $this->getCurrentStrategy()));
        }

        if ($externalName !== '' && is_string($externalName) && $externalUrl !== '' && is_string($externalUrl))
        {
            $this->addPackage($externalName, new UrlPackage($externalUrl, $this->getCurrentStrategy()));
        }
    }
}
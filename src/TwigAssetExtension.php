<?php
namespace LoveCoding\TwigAsset;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use Symfony\Component\Asset\Packages;

class TwigAssetExtension extends AbstractExtension
{
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * Register function to twig template enginee
     *
     * @return array
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('asset', [$this, 'asset'])
        ];
    }

    /********************************************************************************
     * Methods
     *******************************************************************************/

    public function asset(string $path, string $packageName = '') : string
    {
        return $packageName === ''  ? $this->packages->getUrl($path)
                                    : $this->packages->getUrl($path, $packageName);
    }
}
<?php

/*
 * This file is part of the Puli package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Puli\LocatorDumper;

use Webmozart\Puli\Locator\ResourceLocatorInterface;
use Webmozart\Puli\Resource\DirectoryResourceInterface;
use Webmozart\Puli\Resource\ResourceInterface;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PhpResourceLocatorDumper implements ResourceLocatorDumperInterface
{
    const FILE_PATHS_FILE = 'resources_file_paths.php';

    const DIR_PATHS_FILE = 'resources_dir_paths.php';

    const ALTERNATIVE_PATHS_FILE = 'resources_alt_paths.php';

    const TAGS_FILE = 'resources_tags.php';

    public function dumpLocator(ResourceLocatorInterface $locator, $targetPath)
    {
        $filePaths = array();
        $dirPaths = array();
        $alternativePaths = array();
        $tags = array();

        // Extract the paths and alternative paths of each resource
        $this->extractPaths($locator->get('/'), $filePaths, $dirPaths, $alternativePaths);

        // Remember which resource has which tag
        foreach ($locator->getTags() as $tag) {
            $resources = array();

            foreach ($tag->getResources() as $resource) {
                $resources[] = $resource->getRepositoryPath();
            }

            $tags[$tag->getName()] = $resources;
        }

        // Create the directory if it doesn't exist
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        if (!is_dir($targetPath)) {
            throw new \InvalidArgumentException(sprintf(
                'The path "%s" is not a directory.',
                $targetPath
            ));
        }

        file_put_contents($targetPath.'/'.self::FILE_PATHS_FILE, "<?php\n\nreturn ".var_export($filePaths, true).";");
        file_put_contents($targetPath.'/'.self::DIR_PATHS_FILE, "<?php\n\nreturn ".var_export($dirPaths, true).";");
        file_put_contents($targetPath.'/'.self::ALTERNATIVE_PATHS_FILE, "<?php\n\nreturn ".var_export($alternativePaths, true).";");
        file_put_contents($targetPath.'/'.self::TAGS_FILE, "<?php\n\nreturn ".var_export($tags, true).";");
    }

    private function extractPaths(ResourceInterface $resource, array &$filePaths, array &$dirPaths, array &$alternativePaths)
    {
        $repositoryPath = $resource->getRepositoryPath();
        $altPaths = $resource->getAlternativePaths();

        if ($resource instanceof DirectoryResourceInterface) {
            $dirPaths[$repositoryPath] = $resource->getPath();
        } else {
            $filePaths[$repositoryPath] = $resource->getPath();
        }

        // Discard the current path, we already have that information
        if (count($altPaths) > 1) {
            array_pop($altPaths);

            $alternativePaths[$repositoryPath] = $altPaths;
        }

        // Recurse into the contents of directories
        if ($resource instanceof DirectoryResourceInterface) {
            foreach ($resource as $child) {
                $this->extractPaths($child, $filePaths, $dirPaths, $alternativePaths);
            }
        }
    }
}

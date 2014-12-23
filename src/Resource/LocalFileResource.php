<?php

/*
 * This file is part of the puli/repository package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Repository\Resource;

use Assert\Assertion;
use Puli\Repository\UnsupportedResourceException;

/**
 * Represents a file on the local file system.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LocalFileResource extends AbstractLocalResource implements FileResource
{
    /**
     * {@inheritdoc}
     */
    public function __construct($localPath, $path = null, OverriddenPathLoader $pathLoader = null)
    {
        Assertion::file($localPath);

        parent::__construct($localPath, $path, $pathLoader);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return file_get_contents($this->getLocalPath());
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return filesize($this->getLocalPath());
    }

    /**
     * {@inheritdoc}
     */
    public function getLastAccessedAt()
    {
        return fileatime($this->getLocalPath());
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModifiedAt()
    {
        return filemtime($this->getLocalPath());
    }

    /**
     * {@inheritdoc}
     */
    public function override(Resource $file)
    {
        if (!($file instanceof FileResource && $file instanceof LocalResource)) {
            throw new UnsupportedResourceException('Can only override other local file resources.');
        }

        parent::override($file);
    }
}
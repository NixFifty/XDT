<?php

class XDT_Model_AddOn extends XFCP_XDT_Model_AddOn
{
    protected $_annoyingFilenames = array(
        '.DS_Store', // mac specific
        '.localized', // mac specific
        'Thumbs.db' // windows specific
    );

    public function createDir($destination)
    {
        try
        {
            @mkdir($destination, $mode = 0777, $recursive = true);
        }
        catch (Exception $e)
        {
            return false;
        }

        $this->_directoryCleanUp($destination);

        return true;
    }

    /**
     * Recursively copies files from a source to a destination
     */
    public function addOnBuilderCopyFiles($source, $destination)
    {
        try
        {
            @mkdir($destination, $mode = 0777, $recursive = true);
            $directory = dir($source);
        }
        catch (Exception $e)
        {
            return false;
        }

        while (FALSE !== ($readdirectory = $directory->read()))
        {
            if ($readdirectory == '.' || $readdirectory == '..')
            {
                continue;
            }
            $PathDir = $source . '/' . $readdirectory;
            if (is_dir($PathDir))
            {
                self::addOnBuilderCopyFiles($PathDir, $destination . '/' . $readdirectory);
                continue;
            }
            copy($PathDir, $destination . '/' . $readdirectory);
        }

        $directory->close();

        $this->_directoryCleanUp($destination);

        return true;
    }

    public function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source))
        {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source))
        {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest))
        {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read())
        {
            // Skip pointers
            if ($entry == '.' || $entry == '..')
            {
                continue;
            }

            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        $this->_directoryCleanUp($dest);
        return true;
    }

    /**
     * Recursively scan directories for presence of annoying files.
     * If found, this function will delete them from the universe.
     * @param $dir
     * @param bool $doDelete
     * @param array $exclude
     */
    protected function _directoryCleanUp($dir, $doDelete = true, $exclude = array('.', '..'))
    {
        $openHandle = opendir($dir);

        while ($readHandle = readdir($openHandle))
        {
            if (in_array($readHandle, $exclude, true))
            {
                continue;
            }

            $macHidden = strpos($readHandle, '._');
            if ($macHidden !== false && $macHidden === 0)
            {
                if ($doDelete === true)
                {
                    @unlink($dir . DIRECTORY_SEPARATOR . $readHandle);
                }
            }

            if (in_array($readHandle, $this->_annoyingFilenames, true))
            {
                if ($doDelete === true)
                {
                    @unlink($dir . DIRECTORY_SEPARATOR . $readHandle);
                }
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . $readHandle))
            {
                self::_directoryCleanUp($dir . DIRECTORY_SEPARATOR . $readHandle, $doDelete, $exclude);
                continue;
            }
        }

        closedir($openHandle);
    }
}
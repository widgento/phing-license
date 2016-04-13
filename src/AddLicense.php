<?php

require_once 'phing/Task.php';

class AddLicense extends Task {
    const LICENSE_TEXT_FILE     = 'license.txt';
    const LICENSE_FILE_BASENAME = 'license.';

    private $allowedExtensions = array('css', 'js', 'php', 'phtml', 'xml');
    private $srcPath;
    private $licensePath;
    private $module;

    public function getAllowedExtensions() {
        return $this->allowedExtensions;
    }

    public function setAllowedExtensions($allowedExtensions) {
        $this->allowedExtensions = $allowedExtensions;

        if (is_string($allowedExtensions)) {
            $this->allowedExtensions = explode(',', $allowedExtensions);
        }

        return $this;
    }

    public function setSrcPath($srcPath) {
        $this->srcPath = $srcPath;

        return $this;
    }

    public function setModule($module) {
        $this->module = $module;

        return $this;
    }

    public function setLicensePath($licensePath) {
        $this->licensePath = $licensePath;

        return $this;
    }

    public function getSrcPath() {
        return $this->srcPath;
    }

    public function getModule() {
        return $this->module;
    }

    public function getLicensePath() {
        return $this->licensePath;
    }

    protected function addLicenseSome($path) {
        if (file_exists($path) && !is_dir($path)) {
            $nameParts = explode('.', basename($path));
            $extension = array_pop($nameParts);

            if (in_array($extension, $this->getAllowedExtensions())) {
                $license = str_replace(
                    array('{$module}', '{$year}'),
                    array($this->getModule(), date('Y')),
                    file_get_contents($this->getLicensePath().DIRECTORY_SEPARATOR.static::LICENSE_TEXT_FILE));

                $license = str_replace('{$license}', $license,
                    file_get_contents($this->getLicensePath().DIRECTORY_SEPARATOR.static::LICENSE_FILE_BASENAME.$extension));

                $newContent = file_get_contents($path);
                $newContent = str_replace(array('<?xml version="1.0" encoding="UTF-8"?>', '<?xml version="1.0"?>'), '', $newContent);

                file_put_contents($path, $license.$newContent);
            }

            return true;
        }

        $dir = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);

        foreach ($dir as $child) {
            /* @var $child SplFileInfo */
            if ($dir->isDot()) {
                continue;
            }

            $this->addLicenseSome($child->getRealPath());
        }
    }

    public function main() {
        $this->addLicenseSome($this->getSrcPath());
    }
}

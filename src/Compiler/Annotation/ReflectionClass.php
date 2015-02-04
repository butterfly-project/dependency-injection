<?php

namespace Butterfly\Component\DI\Compiler\Annotation;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ReflectionClass extends \ReflectionClass
{
    /**
     * @var array
     */
    protected static $simpleTrashPattern = array('<?php', '<?', 'namespace ', 'use ', 'class ');

    /**
     * @var array
     */
    protected static $regexpTrashPattern = array(
        '|//[^\\n]+|',       // one-line comment block
        '|/\*[\w\W]+?\*/|',  // multiline comment block
    );

    /**
     * @var array
     */
    protected $useStatements;

    /**
     * @param string $shortType
     * @return string|null
     */
    public function getFullNamespace($shortType)
    {
        if ($this->isAbsoluteNamespace($shortType)) {
            return $shortType;
        }

        $fullNamespace = $this->findInUseStatements($shortType);
        if (null !== $fullNamespace) {
            return $fullNamespace;
        }

        return $this->findInCurrentNamespace($shortType);
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isAbsoluteNamespace($type)
    {
        return ('\\' == $type[0]);
    }

    /**
     * @param string $type
     * @return string|null
     */
    private function findInUseStatements($type)
    {
        $useStatements = $this->getUseStatements();

        $path = explode('\\', $type);
        $base = array_shift($path);

        if (!array_key_exists($base, $useStatements)) {
            return null;
        }

        $path = !empty($path) ? '\\' . implode('\\', $path) : '';

        return $useStatements[$base] . $path;
    }

    /**
     * @param string $type
     * @return null|string
     */
    private function findInCurrentNamespace($type)
    {
        $fullNamespace = sprintf("\\%s\\%s", $this->getNamespaceName(), $type);

        return class_exists($fullNamespace) ? $fullNamespace : null;
    }

    /**
     * @return array
     */
    public function getUseStatements()
    {
        if (null === $this->useStatements) {
            $filecontent   = file_get_contents($this->getFileName());
            $fileHead      = $this->extractFileHead($filecontent);
            $useBlock      = $this->extractUseBlock($fileHead);
            $useBlock      = $this->filterUseBlock($useBlock);
            $useLines      = $this->extractUseLines($useBlock);
            $useLines      = $this->removeFirstSlash($useLines);
            $useStatements = $this->prepareUseStatements($useLines);
            $useStatements = $this->addFirstSlash($useStatements);

            $this->useStatements = $useStatements;
        }

        return $this->useStatements;
    }

    /**
     * @param string $fileContent
     * @return string
     */
    protected function extractFileHead($fileContent)
    {
        $endBlock = sprintf(' %s', $this->getShortName());
        $namePos  = strpos($fileContent, $endBlock) + 1;
        $useBlock = substr($fileContent, 0, $namePos);

        return $useBlock;
    }

    /**
     * @param string $fileHead
     * @return string
     */
    protected function extractUseBlock($fileHead)
    {
        $namespacePos    = strripos($fileHead, 'namespace ');
        $namespaceEndPos = strpos($fileHead, ';', $namespacePos);
        $useBlock        = substr($fileHead, $namespaceEndPos + 1);

        return $useBlock;
    }

    /**
     * @param string $str
     * @return string
     */
    protected function filterUseBlock($str)
    {
        $str = str_ireplace(self::$simpleTrashPattern, '', $str);
        $str = preg_replace(self::$regexpTrashPattern, '', $str);

        return $str;
    }

    /**
     * @param string $useBlock
     * @return array
     */
    protected function extractUseLines($useBlock)
    {
        $rawLines = explode(';', $useBlock);

        $lines = array();

        foreach ($rawLines as $rawLine) {
            $rawSubLines = explode(',', $rawLine);

            foreach ($rawSubLines as $line) {
                $lines[] = trim($line);
            }
        }

        return array_filter($lines);
    }

    /**
     * @param array $strs
     * @return array
     */
    protected function removeFirstSlash(array $strs)
    {
        $newStrs = array();

        foreach ($strs as $str) {
            if ('\\' == $str[0]) {
                $str = substr($str, 1);
            }

            $newStrs[] = $str;
        }

        return $newStrs;
    }

    /**
     * @param array $useStatements
     * @return array
     */
    protected function addFirstSlash(array $useStatements)
    {
        $newStrs = array();

        foreach ($useStatements as $alias => $statement) {
            if ('\\' != $statement[0]) {
                $statement = '\\' . $statement;
            }

            $newStrs[$alias] = $statement;
        }

        return $newStrs;
    }

    /**
     * @param array $lines
     * @return array
     */
    protected function prepareUseStatements(array $lines)
    {
        $statements = array();

        foreach ($lines as $line) {
            if (false !== stripos($line, ' as ')) {
                $mathes = array();
                preg_match('/^([a-zA-Z0-9_\\\\]+) as ([a-zA-Z0-9_]+)/i', $line, $mathes);

                $namespace = $mathes[1];
                $alias     = $mathes[2];

                $statements[$alias] = $namespace;
            } else {
                $lastSlashPosition = strrpos($line, '\\');
                if (false !== $lastSlashPosition) {
                    $alias              = substr($line, $lastSlashPosition + 1);
                    $statements[$alias] = $line;
                } else {
                    $statements[$line] = $line;
                }
            }
        }

        return $statements;
    }
}

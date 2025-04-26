<?php

namespace Base\Composer\Cloner;

use Composer\IO\IOInterface;

abstract class AbstractStub implements StubInterface
{
    public IOInterface $io;

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }

    public function print(string $msg)
    {
        $inputNamespace = $this->getStubInputNamespace();
        $outputNamespace = $this->getStubOutputNamespace();

        $displayLimit = 18;
        $shortInputNamespace = strlen($inputNamespace) > $displayLimit-3 ? substr($inputNamespace, 0, $displayLimit-5) . '...' : $inputNamespace;
        $shortOutputNamespace = strlen($outputNamespace) > $displayLimit-3 ? substr($outputNamespace, 0, $displayLimit-5) . '...' : $outputNamespace;
        
        $prefix = sprintf(
            "    *Aliasing  \033[0;35m%-".$displayLimit."s\033[0m to\033[0;35m %-".$displayLimit."s\033[0m.. %s",
            "\"".$shortInputNamespace."\"",
            "\"".$shortOutputNamespace."\"",
            $msg
        );

        $this->io->write($prefix);
    }

    public function getStubName(): string
    {
        return preg_replace('/Stub$/', '', basename(str_replace('\\', '/', get_class($this))));
    }

    public function getStubPath(): string
    {
        return dirname(__FILE__, 6) . "/stubs/".$this->getStubName();
    }

    public function getStubInputNamespace(): string
    {
        return "Base\\".str_replace("/", "\\", $this->getStubName())."\\";
    }

    public function getStubOutputNamespace(): string
    {
        return "App\\".str_replace("/", "\\", $this->getStubName())."\\";
    }

    public function getClassPath(): string
    {
        return dirname(__FILE__, 4) . "/base-bundle/src/".$this->getStubName();
    }
    public function getClassFiles(): array
    {
        $locationPath = $this->getClassPath();
        if (!is_dir($locationPath)) return [];

        $classes = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($locationPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {

                $fqcn = $this->getStubInputNamespace().$this->getClassNamespace($file).$this->getClassName($file);
                $relativePath = str_replace($locationPath . DIRECTORY_SEPARATOR, '', $file);
                $classes[$fqcn] = $relativePath;                
            }
        }

        return $classes;
    }
    
    public function getClassNamespace(string $classFile): string
    {
        $lines = file($classFile);
        $array = preg_grep('/^namespace /', $lines);
        $namespace = array_shift($array);

        $match = [];
        $prefix = $this->getStubInputNamespace();
        if (preg_match('/^namespace (\\\\?)' . addslashes($prefix) . '(\\\\?)(.*);$/', $namespace, $match)) {
            $array = array_pop($match);
            if (!empty($array)) {
                return $array . "\\";
            }
        }

        return "";
    }

    public function getClassName(string $classFile): string
    {
        return basename($classFile, '.php');
    }

    public function generate()
    {
        foreach($this->getClassFiles() as $className => $classFile) {
            
            $stubFile = $this->getStubPath()."/".$classFile;
            $stubPath = dirname($stubFile);

            $stubNamespace = str_replace("/", "\\", preg_replace("/^Base\//", "App/", dirname(str_replace('\\', '/', $className))));
            $stubClass = basename(str_replace('\\', '/', $className));

            $stubCode = "<?php\n\nnamespace " . $stubNamespace . ";\n\n";
            $stubCode .= "if (!class_exists('$stubClass')) {\n";
            $stubCode .= "    class " . basename($stubClass) . " extends \\" . $className . " {}\n";
            $stubCode .= "}\n";

            @mkdir($stubPath, 0777, true);
            file_put_contents($stubFile, $stubCode);
        }

        $this->print("stubs succesfully generated in ".$this->getStubPath());
    }
}
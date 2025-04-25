<?php

function getProjectDir()
{
    return dirname(__DIR__, 2);
}

function getClassname(string $filename)
{
    $directoriesAndFilename = explode('/', $filename);
    $filename = array_pop($directoriesAndFilename);

    $nameAndExtension = explode('.', $filename);
    return array_shift($nameAndExtension);
}

function getAllClasses(string $path, string $prefix = "", int $level = -1): array
{
    $fullpath = realpath($path) . " " . $prefix ." (".$level.")";
    if (!array_key_exists($fullpath, self::$classes ?? [])) {
    
        self::$classes[$fullpath] = self::$classes[$fullpath] ?? [];
        foreach (self::getFiles($path, $level) as $filename) {

            if (filesize($filename) == 0) {
                continue;
            }

            if (str_ends_with($filename, "Interface.php")) {
                continue;
            }

            self::$classes[$fullpath][] = self::getFullNamespace($filename, $prefix) . self::getClassname($filename);
        }

        self::$classes[$fullpath] = array_unique(self::$classes[$fullpath]);
    }

    return self::$classes[$fullpath];
}

function getFullNamespace(string $filename, string $prefix = "")
{
    $lines = file($filename);
    $array = preg_grep('/^namespace /', $lines);
    $namespace = array_shift($array);

    $match = [];
    if (preg_match('/^namespace (\\\\?)' . addslashes($prefix) . '(\\\\?)(.*);$/', $namespace, $match)) {
        $array = array_pop($match);
        if (!empty($array)) {
            return $array . "\\";
        }
    }

    return "";
}


function generateStubs(string $path, string $inputNamespace, string $outputNamespace) {
    
    $output = getProjectDir()."/stubs";
    if (!is_dir($output)) {
        mkdir($output, 0777, true);
    }

    foreach (getAllClasses($path, $inputNamespace) as $rootClass) {

        $outputClass = $outputNamespace . "\\" . $rootClass;
        $inputClass  = $inputNamespace  . "\\" . $rootClass;
        if (class_exists($outputClass, false)) {
            if (!is_subclass_of($outputClass, $inputClass)) {
                throw new \LogicException("According to the base convention, $outputClass must extend $inputClass.");
            }
            continue;
        }

        $outputClassPath = str_replace('\\', '/', $outputClass);
        $stubPath = $output . '/' . $outputClassPath . '.php';
        
        $namespace = str_replace('/', '\\', dirname($outputClassPath));
        
        $stubCode = "<?php\n\nnamespace " . $namespace . ";\n\n";
        $stubCode .= "if (!class_exists('$outputClass')) {\n";
        $stubCode .= "    class " . basename($outputClassPath) . " extends \\" . $inputClass . " {}\n";
        $stubCode .= "}\n";

        @mkdir(dirname($stubPath), 0777, true);
        file_put_contents($stubPath, $stubCode);
    }
}

generateStubs(getBundleDir() . "/src/Entity"    , "Base\Entity"    , "App\Entity");
generateStubs(getBundleDir() . "/src/Repository", "Base\Repository", "App\Repository");
generateStubs(getBundleDir() . "/src/Enum"      , "Base\Enum"      , "App\Enum");

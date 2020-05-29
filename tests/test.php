<?php
if (PHP_SAPI !== 'cli') {
    throw new Exception("This is CLI tool");
}
$inputs = array_slice($argv, 1);
if (empty($inputs)) {
    echo "You need to specify folder";
    exit;
}
$testTool = new UnitTestTool();
$testTool->logInfoMessage('Welcome to Unit Testing');

$files = [];
$testTool->getDirContents(__DIR__ . DIRECTORY_SEPARATOR . $inputs[0], $files);
foreach ($files as $fileName => $true) {
    $pathinfo = pathinfo($fileName);
    if ($pathinfo['extension'] === 'php') {
        include_once $fileName;
    }
}
$testCases = $testTool->getClasses(BaseTest::class);
foreach ($testCases as $class) {
    $testTool->RunTest($class);
}
$testTool->WriteSummary();
//$testTool->debug($testCases);

// classes
class UnitTestScope
{
    private $_data;
    private UnitTestTool $tool;
    private string $workingDirecory;
    public function __construct(UnitTestTool $tool, string $workingDirecory)
    {
        $this->tool = $tool;
        $this->workingDirecory = $workingDirecory;
    }
    public function WorkingDirectory(): string
    {
        return $this->workingDirecory;
    }
    public function this($data)
    {
        $this->_data = $data;
        return $this;
    }
    public function equalsTo(string $content)
    {
        if ($this->_data !== $content) {
            throw new Exception("Two data are not equal");
        }
        return $this;
    }
}

class BaseTest
{
}

class UnitTestTool
{
    private int $TotalCount = 0;
    private int $FailedCount = 0;
    private float $startedAt;
    function __construct()
    {
        $this->startedAt = microtime(true);
    }
    function RunTest(ReflectionClass $testClass)
    {
        $className = $testClass->name;
        $instance = new $className();
        $methods = $testClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $fileLocation = $this->getTitleMessage(str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $testClass->getFileName()));
        $this->logInfoMessage("----------------------------------------------------------------------");
        $this->logInfoMessage("Running tests: $fileLocation");
        $workingDir = $this->createTempDir($className);
        foreach ($methods as $method) {
            $methodName = $method->name;
            $scope = new UnitTestScope($this, $workingDir);

            try {
                $testName = $this->getTitleMessage("$methodName");
                $this->logInfoMessage(" Test: $testName");
                $instance->$methodName($scope);
                $this->logSuccessMessage("   PASSED");
            } catch (Throwable $error) {
                $this->FailedCount++;
                $this->logErrorMessage("   FAILED\n   {$error->getMessage()}\n");
            } finally {
                $this->TotalCount++;
            }
        }
        $this->removeDirectory($workingDir, true);
        // $this->debug(get_included_files());
    }
    function WriteSummary()
    {
        $this->logInfoMessage("----------------------------------------------------------------------");
        $memoryUsed = memory_get_usage(true) / (1024 * 1024);
        $time = floor((microtime(true) - $this->startedAt) * 1000);
        $this->logInfoMessage("Time: {$time}ms, Memory: {$memoryUsed}Mb");
        $this->logInfoMessage("");
        if ($this->FailedCount > 0) {
            $this->logSummaryFail("FAIL ({$this->FailedCount} failed, Total: {$this->TotalCount})");
        } else {
            $this->logSummarySuccess("OK ({$this->TotalCount} tests)");
        }
        //$this->logSuccessMessage(date("m.d.y g:i a") . "\tCompleted");
    }
    function logSummarySuccess(string $message){
        echo "\033[42;97m$message\033[0m\n";
    }
    function logSummaryFail(string $message){
        echo "\033[101;97m$message\033[0m\n";
    }
    function getTitleMessage(string $message)
    {
        return "\033[32m$message\033[0m";
    }
    function logInfoMessage(string $message)
    {
        echo "\033[97m$message\033[0m\n";
    }
    function logSuccessMessage(string $message)
    {
        echo "\033[92m$message\033[0m\n";
    }
    function logErrorMessage(string $message)
    {
        echo "\033[91m$message\033[0m\n";
    }

    public function getClasses(string $baseClass): array
    {
        $children  = array();
        $types = get_declared_classes();
        foreach ($types as $class) {
            $rf = new ReflectionClass($class);
            if (is_subclass_of($class, $baseClass)) {
                $children[$class] = $rf;
            }
        }
        return $children;
    }

    function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[$path] = true;
            } else if ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
            }
        }

        return $results;
    }
    function debug($any, bool $checkEmpty = false): void
    {
        if ($checkEmpty && empty($any)) {
            return;
        }
        print_r($any);
    }
    private function createTempDir($baseName): string
    {
        $dir = 'temp' . DIRECTORY_SEPARATOR . $baseName . DIRECTORY_SEPARATOR . uniqid();
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }
    private function removeDirectory($path, $removeRoot = false)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file, true) : unlink($file);
        }
        if ($removeRoot) {
            rmdir($path);
        }
        return;
    }
}

<?php
if (PHP_SAPI !== 'cli') {
    throw new Exception("This is CLI tool");
}
$inputs = array_slice($argv, 1);
if (empty($inputs)) {
    echo "You need to specify arguments";
    exit;
}
$testTool = new UnitTestTool();
if (count($inputs) === 1) {
    $testTool->Start($inputs[0]);
} else {
    $command = $inputs[0];
    $arguments = array_slice($inputs, 1);
    // echo "Running $command command\n";
    // $testTool->debug($arguments);
    switch ($command) {
        case 'run': {
                // php test.php run backend\\VerifyRender\\ReturnRender.test.php ReturnRenderingTest CanRenderNamedSlots
                list($file, $class, $method) = $arguments;
                $testTool->StartTestFile($file, $class, $method);
                break;
            }
        default: {
                echo "Command $command not found.\n";
            }
    }
}
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
    public function lessThan(float $number)
    {
        if ($this->_data >= $number) {
            throw new Exception("Number should be less than $number");
        }
        return $this;
    }
    public function isNotEmpty()
    {
        if (!$this->_data) {
            throw new Exception("Content should not be empty");
        }
        return $this;
    }
    public function equalsToHtml(string $content)
    {
        $regx = '/(\s)*\n(\s)*/i';
        $result = preg_replace($regx, " ", $this->_data);
        $expected = preg_replace($regx, " ", $content);
        // var_dump($result);
        // var_dump($expected);
        if ($result !== $expected) {
            throw new Exception("Two html contents are not equal");
        }
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
    function Start($testsFolder)
    {
        $this->logInfoMessage('*');
        $this->logInfoMessage('*');
        $this->logInfoMessage('*');
        $this->logInfoMessage('*');
        $this->logInfoMessage('*');
        $this->logInfoMessage('*');
        $this->logInfoMessage('*');
        $this->logInfoMessage('*');
        $this->logInfoMessage('Welcome to Unit Testing');

        $files = [];
        $this->getDirContents(__DIR__ . DIRECTORY_SEPARATOR . $testsFolder, $files);
        foreach ($files as $fileName => $true) {
            if ($this->endsWith($fileName, '.test.php')) {
                include_once $fileName;
            }
        }
        $testCases = $this->getClasses(BaseTest::class);
        foreach ($testCases as $class) {
            $this->ExecuteTestCase($class);
        }
        $this->WriteSummary();
    }
    private function ExecuteTestCase(ReflectionClass $reflectionClass)
    {
        $fileLocation = $this->getTitleMessage(
            str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $reflectionClass->getFileName())
        );
        $this->logInfoMessage("----------------------------------------------------------------------");
        $this->logInfoMessage("Running tests: $fileLocation");
        $fileName = escapeshellarg($reflectionClass->getFileName());
        $className = escapeshellarg($reflectionClass->name);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->name === '__construct') {
                continue;
            }
            $methodName = escapeshellarg($method->name);
            $testName = $this->getTitleMessage("$methodName");
            $this->logInfoMessage(" Test: $testName");
            $cmd = "php test.php run $fileName $className $methodName";
            // $this->logInfoMessage(" Executing inside scope: $cmd");
            $output = shell_exec($cmd);
            if ($output && $output[0] === '{') {
                $json = json_decode($output, true);
                $this->FailedCount += $json['Failed'];
                $this->TotalCount += $json['Total'];
                if ($json['Output']) {
                    echo $json['Output'] . PHP_EOL;
                }
                if ($json['Failed'] === 0) {
                    $this->logSuccessMessage("   PASSED");
                } else {
                    $this->logErrorMessage("   FAILED\n   {$json['Error']}\n");
                }
            } else {
                $this->debug($output);
            }
        }
    }

    function StartTestFile(string $fileName, string $className, string $methodName)
    {
        ob_start();
        include_once $fileName;
        $instance = new $className();
        $workingDir = $this->createTempDir($className);
        $scope = new UnitTestScope($this, $workingDir);
        $errorMessage = '';
        try {
            $instance->$methodName($scope);
        } catch (Throwable $error) {
            $this->FailedCount++;
            $errorMessage = $error->getMessage() . PHP_EOL . $error->getFile() . ':' . $error->getLine() . PHP_EOL . $error->getTraceAsString();
        } finally {
            $this->TotalCount++;
            $this->removeDirectory($workingDir, true);
        }
        $this->removeTempDirectory($className);
        $output = ob_get_contents();
        ob_end_clean();
        echo json_encode([
            'Failed' => $this->FailedCount,
            'Total' => $this->TotalCount,
            'Error' => $errorMessage,
            'Output' => $output
        ]);
    }

    private function RunTest(ReflectionClass $testClass)
    {
        $className = $testClass->name;
        $instance = new $className();
        $methods = $testClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $fileLocation = $this->getTitleMessage(str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $testClass->getFileName()));
        $this->logInfoMessage("----------------------------------------------------------------------");
        $this->logInfoMessage("Running tests: $fileLocation");
        foreach ($methods as $method) {
            $methodName = $method->name;
            if ($methodName === '__construct') {
                continue;
            }
            $workingDir = $this->createTempDir($className);
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
                $this->removeDirectory($workingDir, true);
            }
        }
        $this->removeTempDirectory($className);
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
    function logSummarySuccess(string $message)
    {
        echo "\033[42;97m$message\033[0m\n";
    }
    function logSummaryFail(string $message)
    {
        echo "\033[101;97m$message\033[0m\n";
    }
    function getTitleMessage(string $message)
    {
        return "\033[96m$message\033[0m";
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
    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
    public function getClasses(string $baseClass): array
    {
        $children  = array();
        $types = get_declared_classes();
        foreach ($types as $class) {
            $rf = new ReflectionClass($class);
            if (is_subclass_of($class, $baseClass) && $this->endsWith($rf->getFileName(), '.test.php')) {
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
    private function removeTempDirectory($baseName)
    {
        $path = 'temp' . DIRECTORY_SEPARATOR . $baseName;
        $this->removeDirectory($path, true);
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

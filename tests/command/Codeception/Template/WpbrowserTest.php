<?php

namespace Codeception\Template;

use Codeception\Util\Debug;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use function tad\WPBrowser\rrmdir;

class WpbrowserTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;

    protected $workDir;

    /**
     * @var Process
     */
    protected $process;

    public function excludeGeneratedFiles($filePath, $fileContents)
    {
        return true;
    }

    public static function removeHashLines(array $expected, array $current, $file)
    {
        $removeHashLine = static function ($line) {
            return preg_replace('/\\s*\\/\\/\\s*\\[STAMP].*$/uim', '', $line);
        };
        $arr = [
            array_map($removeHashLine, $expected),
            array_map($removeHashLine, $current)
        ];
        return $arr;
    }

    public function test_non_interactive_setup()
    {
        $process = $this->buildProcess(['--no-interaction']);
        $process->mustRun();

        $this->assertEquals(0, $process->getExitCode());

        $this->assertMatchesDirectorySnapshot($this->workDir, [static::class,'removeHashLines']);
    }

    protected function buildProcess(array $args = [])
    {
        $codeceptBinary = codecept_root_dir('vendor/bin/codecept');
        $this->process = new Process(
            array_merge([$codeceptBinary, 'init', 'wpbrowser'], $args),
            $this->workDir
        );

        return $this->process;
    }

    public function test_with_default_answers_interactive_setup()
    {
        $input = new InputStream();
        $answers = [
            'acknowledge' => 'y',
            'interactive' => 'y',
            'acceptanceSuite' => '',
            'functionalSuite' => '',
            'wpunitSuite' => '',
            'envFileName' => '',
            'wpRootFolder' => '',
            'testSiteWpAdminPath' => '',
            'testSiteDbName' => '',
            'testSiteDbHost' => '',
            'testSiteDbUser' => '',
            'testSiteDbPassword' => '',
            'testSiteTablePrefix' => '',
            'testDbName' => '',
            'testDbHost' => '',
            'testDbUser' => '',
            'testDbPassword' => '',
            'testTablePrefix' => '',
            'testSiteWpUrl' => '',
            'testSiteAdminEmail' => '',
            'title' => '',
            'testSiteAdminUsername' => '',
            'testSiteAdminPassword' => '',
            'sut' => '',
            'mainPlugin' => '',
            'activateFurtherPlugins' => 'no',
        ];

        $input->write(implode("\n", $answers));

        $process = $this->buildProcess([]);
        $process->setInput($input);
        $process->start();

        $input->close();
        $process->wait();

        $this->assertMatchesDirectorySnapshot($this->workDir, [static::class,'removeHashLines']);
    }

    public function test_changing_env_file_name()
    {
        $input = new InputStream();
        $answers = [
            'acknowledge' => 'y',
            'interactive' => 'y',
            'acceptanceSuite' => '',
            'functionalSuite' => '',
            'wpunitSuite' => '',
            'envFileName' => '.env.local',
            'wpRootFolder' => '',
            'testSiteWpAdminPath' => '',
            'testSiteDbName' => '',
            'testSiteDbHost' => '',
            'testSiteDbUser' => '',
            'testSiteDbPassword' => '',
            'testSiteTablePrefix' => '',
            'testDbName' => '',
            'testDbHost' => '',
            'testDbUser' => '',
            'testDbPassword' => '',
            'testTablePrefix' => '',
            'testSiteWpUrl' => '',
            'testSiteAdminEmail' => '',
            'title' => '',
            'testSiteAdminUsername' => '',
            'testSiteAdminPassword' => '',
            'sut' => '',
            'mainPlugin' => '',
            'activateFurtherPlugins' => 'no',
        ];

        $input->write(implode("\n", $answers));

        $process = $this->buildProcess([]);
        $process->setInput($input);
        $process->start();

        $input->close();
        $process->wait();

        $this->assertMatchesDirectorySnapshot($this->workDir, [static::class,'removeHashLines']);
    }

    protected function _before()
    {
        $this->workDir = codecept_output_dir('template/project-' . md5(microtime()));
        mkdir($this->workDir, 0777, true);
    }

    protected function _after()
    {
        if (Debug::isEnabled()) {
            return;
        }
        rrmdir($this->workDir);
    }

    protected function _failed()
    {
        if (Debug::isEnabled()) {
            return;
        }

        rrmdir($this->workDir);
    }
}

<?php

namespace tests\Command;

use DeSmart\DeMaker\Core\Command\DTO;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DTOTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('tmp'));
    }

    /** @test */
    public function it_executes()
    {
        $application = new Application();
        $application->add(new DTO());

        $command = $application->find('dto');
        $commandTester = new CommandTester($command);

        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('Foo/Bar.php'));

//        $commandTester->execute([
//            'command' => $command->getName(),
//            'fqn' => 'Foo\Bar',
//            'testfqn' => 'tests\Foo\Bar',
//            '-p' => 'foo:string,bar:\\Carbon\\Carbon'
//        ]);

//        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('Foo'));
//        $this->assertRegExp('/.../', $commandTester->getDisplay());
    }
}

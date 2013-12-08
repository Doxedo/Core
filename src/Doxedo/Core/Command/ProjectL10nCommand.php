<?php

namespace Doxedo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doxedo\Topic;
use LinkORB_Core;

class ProjectL10nCommand extends Command {
    protected function configure()
    {
        $this
            ->setName('project:l10n')
            ->setDescription(
                  'Update translation strings on doxedo project file.'
              )
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'file name'
            );
    }
    
    function execute(InputInterface $input, OutputInterface $output)
    {
        $filename=$input->getArgument('filename');
        
        $output->write("Processing '" . $filename ."'\n");

        //TODO: Handle this through autoloading
        $root = LinkORB_Core::GetPath("root");
        
        $topic = new Topic();
        $topic->name = 'Root';
        if (!$topic->load($filename)) {
            Console::Error("Unable to load map file.");
            exit(1);
        }   
        $topic->TopicL10N();
        $output->write('DONE');
        exit(0);
    }
} 
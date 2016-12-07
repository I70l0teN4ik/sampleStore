<?php

namespace AppBundle\Command;

use AppBundle\Entity\Product;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateProductsCommand extends ContainerAwareCommand
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('app:generate:products')
            ->setDescription('Creates dummy products and stores them in the database')
            ->addArgument('amount', InputArgument::OPTIONAL, 'Amount of new products', 1)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);

        $amount = $count = intval($input->getArgument('amount'));

        while ($amount--) {
            $product = new Product();
            $product->setName($this->getRandomName());
            $product->setDescription($this->getRandomDescription());
            $product->setPrice($this->randomFloat());

            $this->em->persist($product);
        }

        $this->em->flush();

        $output->writeln('');
        $finishTime = microtime(true);
        $elapsedTime = $finishTime - $startTime;

        $output->writeln(sprintf('Success: %d new products was generated / Elapsed time: %.2f ms', $count, $elapsedTime * 1000));
    }

    private function getPhrases()
    {
        return [
            'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis suscipit vulputate',
            'Eros diam egestas libero eu vulputate risus',
            'In hac habitasse platea dictumst',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere justo at vulputate',
            'Ut eleifend mauris et risus ultrices egestas',
            'Aliquam sodales odio id eleifend tristique',
            'Urna nisl sollicitudin id varius orci quam id turpis',
            'Nulla porta lobortis ligula vel egestas',
            'Curabitur aliquam euismod dolor non ornare',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien pulvinar consectetur',
        ];
    }

    private function getRandomName()
    {
        $titles = $this->getPhrases();

        return $titles[array_rand($titles)];
    }

    private function getRandomDescription()
    {
        $phrases = $this->getPhrases();

        $numPhrases = mt_rand(6, 12);
        shuffle($phrases);

        return substr(implode(' ', array_slice($phrases, 0, $numPhrases - 1)), 0, 255);
    }

    private function randomFloat($min = 1, $max = 100) {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\MaintenanceMode;
use Magento\Setup\Model\IpValidator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractMaintenanceCommand extends AbstractSetupCommand
{
    /**
     * Names of input option
     */
    const INPUT_KEY_IP = 'ip';

    /**
     * @var MaintenanceMode
     */
    protected $maintenanceMode;

    /**
     * @var IpValidator
     */
    protected $ipValidator;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(MaintenanceMode $maintenanceMode, IpValidator $ipValidator)
    {
        $this->maintenanceMode = $maintenanceMode;
        $this->ipValidator = $ipValidator;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_IP,
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Allowed IP addresses'
            ),
        ];
        $this->setDefinition($options);
        parent::configure();
    }

    /**
     * Get maintenance mode to set
     *
     * @return bool
     */
    abstract protected function isEnable();

    /**
     * Get display string after mode is set
     *
     * @return string
     */
    abstract protected function getDisplayString();

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addresses = $input->getOption(self::INPUT_KEY_IP);
        $messages = $this->ipValidator->validateIps($addresses, true);
        if (!empty($messages)) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $messages));
            return;
        }

        $this->maintenanceMode->set($this->isEnable());
        $output->writeln($this->getDisplayString());

        if (!empty($addresses)) {
            $addresses = implode(',', $addresses);
            $addresses = ('none' == $addresses) ? '' : $addresses;
            $this->maintenanceMode->setAddresses($addresses);
            $output->writeln(
                '<info>Set exempt IP-addresses: ' . (implode(', ', $this->maintenanceMode->getAddressInfo()) ?: 'none')
                . '</info>'
            );
        }
    }
}

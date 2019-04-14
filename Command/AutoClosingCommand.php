<?php

namespace Hackzilla\Bundle\TicketBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Hackzilla\Bundle\TicketBundle\Entity\Ticket;
use Hackzilla\Bundle\TicketBundle\Entity\TicketMessage;
use Hackzilla\Bundle\TicketBundle\Manager\TicketManagerInterface;
use Hackzilla\Bundle\TicketBundle\Manager\UserManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AutoClosingCommand extends ContainerAwareCommand
{
    /**
     * @var TicketManagerInterface
     */
    private $ticketManager;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $locale = 'en';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TicketManagerInterface $ticketManager, UserManagerInterface $userManager, EntityManagerInterface $entityManager, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {
        parent::__construct();

        $this->ticketManager = $ticketManager;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        if ($parameterBag->has('locale')) {
            $this->locale = $parameterBag->get('locale');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ticket:autoclosing')
            ->setDescription('Automatically close resolved tickets still opened')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username of the user who change the status'
            )
            ->addOption(
                'age',
                'a',
                InputOption::VALUE_OPTIONAL,
                'How many days since the ticket was resolved?',
                '10'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
<<<<<<< Updated upstream
        $ticket_manager = $this->getContainer()->get('hackzilla_ticket.ticket_manager');
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $ticketRepository = $this->getContainer()->get('doctrine')->getRepository('HackzillaTicketBundle:Ticket');

        $locale = $this->getContainer()->getParameter('locale') ? $this->getContainer()->getParameter('locale') : 'en';
        $translator = $this->getContainer()->get('translator');
        $translator->setLocale($locale);
=======
        $ticketRepository = $this->entityManager->getRepository(Ticket::class);

        $this->translator->setLocale($this->locale);
>>>>>>> Stashed changes

        $username = $input->getArgument('username');

        $resolved_tickets = $ticketRepository->getResolvedTicketOlderThan($input->getOption('age'));

        foreach ($resolved_tickets as $ticket) {
            $message = $this->ticketManager->createMessage()
                ->setMessage(
<<<<<<< Updated upstream
                    $translator->trans('MESSAGE_STATUS_CHANGED', ['%status%' => $translator->trans('STATUS_CLOSED')])
=======
                    $this->translator->trans('MESSAGE_STATUS_CHANGED', ['%status%' => $this->translator->trans('STATUS_CLOSED', [], 'HackzillaTicketBundle')], 'HackzillaTicketBundle')
>>>>>>> Stashed changes
                )
                ->setStatus(TicketMessage::STATUS_CLOSED)
                ->setPriority($ticket->getPriority())
                ->setUser($this->userManager->findUserByUsername($username))
                ->setTicket($ticket);

            $ticket->setStatus(TicketMessage::STATUS_CLOSED);
            $this->ticketManager->updateTicket($ticket, $message);

            $output->writeln('The ticket "'.$ticket->getSubject().'" has been closed.');
        }
    }
}

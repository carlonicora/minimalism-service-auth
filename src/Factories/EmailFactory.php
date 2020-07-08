<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Mailer\Interfaces\MailerServiceInterface;
use CarloNicora\Minimalism\Services\Mailer\Mailer;
use CarloNicora\Minimalism\Services\Mailer\Objects\Email;
use Exception;

class EmailFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /**
     * CodeFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param string $template
     * @param string $title
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $senderEmail
     * @param string $senderName
     * @param array $data
     * @throws Exception
     */
    public function sendEmail(
        string $template,
        string $title,
        string $recipientEmail,
        string $recipientName,
        string $senderEmail,
        string $senderName,
        array $data
    ): void
    {
        $paths = [];
        $defaultDirectory = $this->services->paths()->getRoot() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Views';
        if (file_exists($defaultDirectory)){
            $paths[] = $defaultDirectory;
        }

        foreach ($this->services->paths()->getServicesViewsDirectories() as $additionalPaths) {
            $paths[] = $additionalPaths;
        }

        /** @var MailerServiceInterface $mailer */
        $mailer = $this->services->service(Mailer::class);
        $email = new Email(
            $title,
            $paths
        );
        $email->addRecipient($$recipientEmail, $recipientName);

        $email->addTemplateFile($template);
        $email->addParameters($data);
        $mailer->setSender($senderEmail, $senderName);
        $mailer->send($email);
    }
}
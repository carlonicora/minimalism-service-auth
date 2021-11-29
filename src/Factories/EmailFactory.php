<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Interfaces\Mailer\Enums\RecipientType;
use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\TwigMailer\Objects\TwigEmail;
use Exception;

class EmailFactory
{
    /**
     * EmailFactory constructor.
     * @param Path $path
     * @param MailerInterface $mailer
     */
    public function __construct(
        private Path $path,
        private MailerInterface $mailer,
    )
    {
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
        $defaultDirectory = $this->path->getRoot() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Views';
        if (file_exists($defaultDirectory)){
            $paths[] = $defaultDirectory;
        }

        foreach ($this->path->getServicesViewsDirectories() as $additionalPaths) {
            $paths[] = $additionalPaths;
        }

        $email = new TwigEmail(
            new Recipient(
                emailAddress: $senderEmail,
                name: $senderName??'',
                type: RecipientType::Sender,
            ),
            subject: $title,
        );
        $email->addTemplateDirectory($paths);
        $email->addRecipient(
            new Recipient(
                emailAddress: $recipientEmail,
                name: $recipientName??'',
                type: RecipientType::To,
            )
        );

        $email->addTemplateFile($template);
        $email->setParameters($data);
        $this->mailer->send($email);
    }
}
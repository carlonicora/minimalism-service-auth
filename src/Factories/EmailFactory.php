<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Services\Mailer\Mailer;
use CarloNicora\Minimalism\Services\Mailer\Objects\Email;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class EmailFactory
{
    /**
     * EmailFactory constructor.
     * @param Path $path
     * @param Mailer $mailer
     */
    public function __construct(
        private Path $path,
        private Mailer $mailer,
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

        $email = new Email(
            $title,
            $paths
        );
        $email->addRecipient($recipientEmail, $recipientName);

        $email->addTemplateFile($template);
        $email->addParameters($data);
        $this->mailer->setSender($senderEmail, $senderName);
        $this->mailer->send($email);
    }
}
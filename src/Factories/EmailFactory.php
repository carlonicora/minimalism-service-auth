<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Interfaces\SimpleObjectInterface;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\TwigMailer\Objects\TwigEmail;
use Exception;

class EmailFactory implements SimpleObjectInterface
{
    /**
     * EmailFactory constructor.
     * @param Path $path
     * @param MailerInterface $mailer
     * @param Auth $auth
     */
    public function __construct(
        private Path $path,
        private MailerInterface $mailer,
        private Auth $auth,
    )
    {
    }

    /**
     * @param string $template
     * @param array $data
     * @param Recipient $recipient
     * @param string $title
     * @throws Exception
     */
    public function sendEmail(
        string $template,
        array $data,
        Recipient $recipient,

        string $title,
    ): void
    {
        $paths = [];
        $defaultDirectory = $this->path->getRoot() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Views';
        if (file_exists($defaultDirectory)){
            $paths[] = $defaultDirectory;
        }

        $paths = array_merge($paths, $this->path->getServicesViewsDirectories());
        $data['title'] = $title;

        $email = new TwigEmail(
            sender: $this->auth->getSender(),
            subject: $title,
        );
        $email->addTemplateDirectory($paths);
        $email->addRecipient($recipient);
        $email->addTemplateFile($template . '.twig');
        $email->setParameters($data);

        $this->mailer->send($email);
    }
}
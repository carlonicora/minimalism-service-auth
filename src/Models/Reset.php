<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\IO\CodeIO;
use CarloNicora\Minimalism\Services\OAuth\IO\AppIO;
use Exception;

class Reset extends AbstractAuthWebModel
{
    /**
     * @param PositionedEncryptedParameter $userId
     * @param PositionedParameter $code
     * @param PositionedParameter $clientId
     * @param PositionedParameter $state
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        PositionedEncryptedParameter $userId,
        PositionedParameter $code,
        PositionedParameter $clientId,
        PositionedParameter $state,
    ): HttpCode
    {
        $this->view = 'reset';
        $this->auth->setClientId($clientId->getValue());
        $this->auth->setState($state->getValue());

        try {
            $user = $this->auth->getAuthenticationTable()->authenticateById($userId->getValue());
        } catch (Exception) {
            throw ExceptionFactory::MissingUserInformation->create();
        }

        $isCodeValid = $this->objectFactory->create(CodeIO::class)->isCodeValid($user->getId(), $code->getValue());
        $this->document->meta->add(name: 'isCodeValid', value: $isCodeValid);

        if ($isCodeValid){
            $this->document->meta->add(name: 'validCode', value: $code->getValue());

            $this->document->addResource(new ResourceObject('user', $userId->getEncryptedValue()));
            $this->document->meta->add(name: 'code', value: $code->getValue());

            try {
                $app = $this->objectFactory->create(AppIO::class)->readByClientId($this->auth->getClientId());

                $this->document->links->add(
                    new Link('doCancel', $app->getUrl())
                );
            } catch (Exception) {
            }
        } else {
            $this->document->links->add(
                new Link('doCancel', $this->url . 'auth?client_id=' . $clientId->getValue() . '&state=' . $state->getValue())
            );
        }

        return HttpCode::Ok;
    }

    /**
     * @param EncryptedParameter $userId
     * @param int $code
     * @param string $password
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        EncryptedParameter $userId,
        int $code,
        string $password,
    ): HttpCode
    {
        $user = $this->auth->getAuthenticationTable()->authenticateById($userId->getValue());

        $this->objectFactory->create(CodeIO::class)->validate($user->getId(), $code);
        $this->auth->getAuthenticationTable()->updatePassword($user->getId(), password_hash($password, PASSWORD_BCRYPT));
        /** @noinspection RepetitiveMethodCallsInspection */
        if(!$user->isActive()) {
            $this->auth->getAuthenticationTable()->activateUser($user);
        }
        $this->auth->setUserId($user->getId());

        $this->addCorrectRedirection();

        return HttpCode::NoContent;
    }
}
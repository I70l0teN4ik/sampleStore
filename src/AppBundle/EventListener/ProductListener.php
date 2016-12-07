<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Product;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class ProductListener
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $product = $event->getObject();

        if ($product instanceof Product) {
            $body = sprintf('New product %s created.', $product->getName());

            $message = \Swift_Message::newInstance();
            $message->setSubject('New Product Notification');
            $message->setFrom('sample.store@example.com', 'Sample Store App');
            $message->setTo('fake@example.com');
            $message->setBody($body, 'text/html');

            $this->mailer->send($message);
        }
    }
}

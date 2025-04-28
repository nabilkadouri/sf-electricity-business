<?php

namespace App\EventListener;


use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(Events::prePersist)]
class HashUserPasswordListener
{
    // Déclaration du constructeur avec une dépendance injectée pour le hachage des mots de passe
    public function __construct(
        private UserPasswordHasherInterface $hasher ) {
    }

    // Méthode appelée avant la persistance d'une entité en base de données
    public function prePersist(PrePersistEventArgs $event): void
    {
        // Récupération de l'objet concerné par l'événement
        $entity = $event->getObject();

        // Vérification si l'objet est une instance de la classe User
        if (!$entity instanceof User) {
            return; // Si ce n'est pas un User, on ne fait rien et on retourne
        }

        // Hachage du mot de passe de l'utilisateur en utilisant le hasher injecté
        $entity->setPassword(
            $this->hasher->hashPassword($entity, $entity->getPassword())
        );
    }
}
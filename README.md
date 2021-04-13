# SonataDTOAdminBundle

Alternative au bundle [SonataDoctrineOrmAdminBundle](https://github.com/sonata-project/SonataDoctrineORMAdminBundle) pour la gestion des admins
sonata. Ce bundle permet d'utiliser des [DTOs](https://en.wikipedia.org/wiki/Data_transfer_object) dans les admins.

Il peut être utilisé par exemple pour afficher des données récupérées d'une api (ou de n'importe quelle source autre que Doctrine).
dans un admin Sonata

## Installation

Le bundle peut être installer avec [Composer](https://getcomposer.org/).
* Il faut ajouter le repo vtech dans la configuration de composer:

```json
"repositories": {
    "vtech": {
        "type": "composer",
        "url": "https://composer.vtech.fr"
    }
}
```

* Puis taper la commande suivante:

```shell
composer require vtech-bundles/sonata-dto-admin-bundle
```

## Utilisation

### Configuration de l'admin

Pour utiliser ce bundle un admin doit être déclaré dans Sonata comme étant de type `dto`.
Ci-dessous un exemple de configuration en yaml:

```yaml
tags:
  - { name: sonata.admin, manager_type: dto, label: 'my_admin_label' }
```

De plus pour fonctionner un classe implémentant l'interface `Vtech\Bundle\SonataDTOAdminBundle\Repository\AdminRepositoryInterface`
doit être créée pour indiquer comment récupérer, sauvegarder et modifier le DTO.
Cette classe "repository" doit être taguée dans l'injection de dépendance de la façon suivante:

```yaml
tags:
  - { name: 'sonata.admin.dto_repository', class: 'My\Dto\Class' }
```

Pour tirer profit de l'auto configuration sur les dernières versions de Symfony, une interface de "subscriber" existe également
(`Vtech\Bundle\SonataDTOAdminBundle\Repository\AdminRepositoryInterface`) pour supprimer l'argument `class` du tag.
Il vous suffira d'ajouter ceci dans votre fichier `services.yaml` (pour Symfony ^3.3):

```yaml
_instanceof:
  Vtech\Bundle\SonataDTOAdminBundle\Repository\AdminRepositoryInterface:
    tags: [sonata.admin.dto_repository]
```

Il existe 4 autres interfaces facultatives dans ce bundle :

| Interface | Description |
| :--- | :--- |
| `Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierDescriptorInterface` | Défini les champs qui composent l'identifiant unique du DTO |
| `Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierDenormalizerInterface` | Défini comment dénormaliser l'identifiant unique du DTO depuis un string |
| `Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierNormalizerInterface` | Défini comment normaliser l'identifiant unique du DTO en string |

Les classes implémentant ces interfaces doivent être déclarées dans l'injection de dépendance de la même manière que le "repository" :

```yaml
tags:
  # Pour Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierNormalizerInterface
  - { name: 'sonata.admin.dto_identifier_normalizer', class: 'My\Dto\Class' }
  # Pour Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierDenormalizerInterface
  - { name: 'sonata.admin.dto_identifier_denormalizer', class: 'My\Dto\Class' }
  # Pour Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierDescriptorInterface
  - { name: 'sonata.admin.dto_identifier_descriptor', class: 'My\Dto\Class' }
```

### Liste des filtres disponibles

Lorsqu'un admin est de type `dto` il ne peut pas utiliser les filtres par défaut de Sonata dans la méthode `configureDatagridFilters`.
Il doit obligatoirement utiliser l'un des filtres suivants (ou en créer un nouveau) :

| Interface | Alias |
| :--- | :--- |
| `Vtech\Bundle\SonataDTOAdminBundle\Filter\BooleanFilter` | `dto_boolean` |
| `Vtech\Bundle\SonataDTOAdminBundle\Filter\CallbackFilter` | `dto_callback` |
| `Vtech\Bundle\SonataDTOAdminBundle\Filter\DateFilter` | `dto_date` |
| `Vtech\Bundle\SonataDTOAdminBundle\Filter\DoctrineEntityFilter` | `dto_doctrine_entity` |
| `Vtech\Bundle\SonataDTOAdminBundle\Filter\DefaultFilter` | `dto_default` |
| `Vtech\Bundle\SonataDTOAdminBundle\Filter\StringFilter` | `dto_string` |

## Publication

Pour publier une nouvelle version de ce bundle il suffit de créer un nouveau tag dans GitLab puis de mettre à jour
le repo satis interne [à cette adresse](http://satis.vtech.fr/update.php?repository_url=ssh://git@git.vtech.fr:888/vtech-bundles/SonataDTOAdminBundle.git).


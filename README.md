EdulogDatatablesBundle
====

Le EdulogDatatablesBundle permet de créer des tableaux en utilisant le plugin js Datatables
et le bundle OminesDatatablesBundle (https://github.com/omines/datatables-bundle)


Installation
============

Step 1: Download the Bundle

```console
$ composer require edulog/datatables-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 2: Enable the Bundles

Il faut avoir configuré FosJsRoutingBundle, OminesDataTablesBundle et WebpackEncoreBundle

https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
https://github.com/omines/datatables-bundle
https://github.com/symfony/webpack-encore-bundle

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new \Omines\DataTablesBundle\DataTablesBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Edulog\DatatablesBundle\EdulogDatatablesBundle(),
            new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
        ];

        // ...
    }

    // ...
}
```

### Step 3: Mettre les config

WebpackEncore :

```yaml
// app/config.yml

webpack_encore:
    output_path: "%kernel.root_dir%/../web/build"
    
```

OminesDatatables

```yaml
datatables:

  # Load i18n data from DataTables CDN or locally
  language_from_cdn:    true

  # Default HTTP method to be used for callbacks
  method:               POST # One of "GET"; "POST"

#  # Default options to load into DataTables
#  options:
#    pageLength:           30

  # Where to persist the current table state automatically
  persist_state:        local # One of "none"; "query"; "fragment"; "local"; "session"

  # Default service used to render templates, built-in TwigRenderer uses global Twig environment
  renderer:             Omines\DataTablesBundle\Twig\TwigRenderer

  # Default template to be used for DataTables HTML
  template:             '@DataTables/datatable_html.html.twig'

  # Default parameters to be passed to the template
  template_parameters:

    # Default class attribute to apply to the root table elements
    className:        'table table-hover table-striped responsive'

    # If and where to enable the DataTables Filter module
    columnFilter:     both # One of "thead"; "tfoot"; "both"; null

  # Default translation domain to be used
  translation_domain:   messages
```





### Step 4 : Implémentation dans le controller avec un formulaire de filtrage

Injecter le dtFilterFactory dans le constructeur du controller et initialiser
le dtFilter associé.

FormSearchType peut contenir différent champs (TextType, ChoiceType etc ..) qui serviront à
filtrer la requete dans le repository.

Constructeur

```php
   
    public function __construct(DtFilterFactory $dtFilterFactory)
    {
        $this->dtFilterFactory = $dtFilterFactory;
        $this->dtFilter = $dtFilterFactory->getObject(FormSearchType::class);

    }
```   

Méthode affichant la page contenant le tableau de données
Il s'agit ici du fonctionnement classique du OminesDatatablesBundle.

Le seul ajout correpond au formulaire de filtrage qui est initialisé de façon classique. Le second argument
correspond au dtFilter issu du constructeur.

Quelques options sont disponibles en argument de la méthode createFromType notamment
le pageLength qui permet de déterminer le nombre d'élemenet qui seront affichés dans le tableau.
Il est utile dans la mesure ou c'est ce paramètre qui permet de faire le setMaxResult sur la méthode 
de repository associé

Liste des options : (la totalité de ces options est également disponible via le JS)

```php
[
    'pageLength' => 50,
    'dom' => '<"row"<"col-3"l><"col-6 text-center"i><"col-3"f>>rt<"row"<"col-12 text-center"i>><"row"<"col-12"p>><"clear">',
    'paging' => true,
    'searching' => false,
    'stateSave' => true,
    'displayStart' => '',
    'lengthChange' => false, // display / hide select change length
    'order' => '',
    'orderCellsTop', '',
    'ordering' => '',
    'pagingType' => "full",
    'processing' => '',
    'search' => true,
    'searchDelay' => true,
    'serverSide' => true,
]
````

```php    
    public function indexAction(Request $request, DataTableFactory $dataTableFactory)
    {
        $form = $this->createForm(FormSearchType::class, $this->dtFilter);
        $form->handleRequest($request);
        
        $table = $dataTableFactory->createFromType(DtStudentSearchType::class, [], [
            'pageLength' => 50,
            'paging' => true,
        ])
            ->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('view.html.twig', [
            'form' => $form->createView(),
            'datatable' => $table
        ]);
    }
```

La classe DtTestSearchType

OminesDatatables bundle recommande de créer une classe à part pour éviter d'avoir 
une méthode de controller HUGE..

```php

<?php

class DtStudentSearchType extends DtSearchType
{
    protected $personRepository;

    /**
     * Override du constructeur pour charger le dtFilter avec les données du FormSearchType.
     *
     * DtStudentSearchType constructor.
     * @param EntityManagerInterface $em
     * @param EngineInterface $templating
     * @param RouterInterface $router
     * @param DtFilterFactory $dtFilterFactory
     */
    public function __construct(EntityManagerInterface $em, EngineInterface $templating, RouterInterface $router, DtFilterFactory $dtFilterFactory)
    {
        parent::__construct($em, $templating, $router, $dtFilterFactory);
        $this->dtFiler = $this->dtFilterFactory->getObject(FormSearchType::class);
        $this->personRepository = $this->em->getRepository(Person::class);
    }

    /**
     * Configuration des colonnes + tri par défaut par lastname ASC.
     * En utilisant le Query::HYDRATE_OBJECT je n'arrive pas à faire de mapping "automatique", j'utilise donc l'option
     * render sur chaque colonne pour aller chercher la valeur à afficher.
     *
     * En utilisant Query::HYDRATE_ARRAY il est possible de se passer du render en précisant le propertyPath
     * pour aller chercher les données.
     *
     * Les orderBy des colonnes sont effectués automatiquement via le CustomORMAdapter.
     *
     * La requete issu du builder donne un truc dans ce genre pour chaque person :
     * [
     *     [0] => PersonObject,
     *      [
     *          'classroomTitle' => "BTS",
     *          'unitColor' => '#fffff",
     *          'regimeTitle => 'Externe'
     *      ]
     * ]
     *
     *
     * @param DataTable $dataTable
     * @param array $options
     */
    public function configure(DataTable $dataTable, array $options)
    {
        $dataTable
            ->setMethod('POST') // OR GET

            ->add('details', TextColumn::class, [
                'label' => '',
                'visible' => true,
                'searchable' => false,
                'orderable' => false,
                'className' => 'details-control'
            ])

            ->add('id', TextColumn::class, [
                'label' => '#',
                'field' => 'p.id',
                //'propertyPath' => '[0][id]',
                'visible' => false,
                'render' => function($value, $context) {
                    /** @var Person $person */
                    $person = $context[0];
                    return $person->getId();
                }
            ])

            ->add('lastName', TextColumn::class, [
                'label' => 'Nom',
                'searchable' => true,
                'globalSearchable' => true,
                'propertyPath' => '[0][lastName]',
                'render' =>  function($value, $context) {
                    /** @var Person $person */
                    $person = $context[0];
                    return $person->getLastName();
                }
            ])

            ->add('firstName', TextColumn::class, [
                'label' => 'Prénom',
                'searchable' => true,
                'globalSearchable' => true,
                //'field' => 'firstName'
                //'propertyPath' => '[0][firstname]',
                'render' =>  function($value, $context) {
                    /** @var Person $person */
                    $person = $context[0];
                    return $person->getFirstName();
                }
            ])
            
            ->add('classroomTitle', TextColumn::class, [
                'label' => 'Classe',
                'raw' => true,
                'orderable' => true,
                'searchable' => false,
                'globalSearchable' => false,
                'field' => 'classroomTitle',
                'propertyPath' => '[classroomTitle]',
                'render' =>  function($value, $context) {

                    return $this->templating->render(':Twig:render_classroom_by_unit_color.html.twig', [
                        'title' => $context['classroomTitle'],
                        'color' => $context['unitColor']
                    ]);
                }
            ])

            ->add('regimeTitle', TextColumn::class, [
                'label' => 'Régime',
                'orderable' => true,
                'raw' => true,
                'searchable' => false,
                'globalSearchable' => false,
                'field' => 'regimeTitle',
                'propertyPath' => '[regimeTitle]',
                'render' => function($value, $context) {
                    return $this->templating->render(':Twig:render_badge.html.twig', [
                        'text' => $value
                    ]);
                }
            ])

            ->add('responsibles', TextColumn::class, [
                'label' => 'Responsable(s)',
                'orderable' => false,
                'searchable' => false,
                'raw' => true,
                'globalSearchable' => false,
                // Permet de cacher cette colonne mais de pouvoir l'afficher via le bouton +
                'className' => 'none',
                'render' => function($value, $context) {
                    /** @var Person $person */
                    $person = $context[0];
                    $responsibleStudents = $person->getStudent()->getResponsibleStudents();
                    /** @var ResponsibleStudent $rs */
                    $stringToReturn = '';
                    foreach ($responsibleStudents as $rs) {
                        $personRs = $rs->getResponsible()->getPerson();
                        $stringToReturn .= '<div>' . $this->templating->render(':Twig:render_person.html.twig', [
                            'person' => $personRs
                        ]) . '</div>';
                    }

                    return $stringToReturn;
                }
            ])

            ->add('actions', TextColumn::class, [
                'className' => 'text-right',
                'label' => 'Actions',
                'raw' => true,
                'render' => function($value, $context) {
                    /** @var Person $person */
                    $person = $context[0];
                    return $this->templating->render('@StudentBase/Person/Student/partial/buttons_student_search.html.twig', [
                        'idPerson' => $person->getId()
                    ]);
                }
            ])
            
            // Permet d'ajouter un tri par défaut ici sur la colonne lastName.
            ->addOrderBy('lastName', DataTable::SORT_ASCENDING)
            
            // Le customORMAdapter permet notamment d'ajotuer les orderBy en fonction des colonnes
            // qui ont été sélectionnées
            ->createAdapter(CustomORMAdapter::class, [
                'entity' => Person::class,
                
                // Permet d'obtenir des resultat sous forme d'objet ou d'array.
                'hydrate' => Query::HYDRATE_OBJECT, //Query::HYDRATE_ARRAY,
                
                // Permet de définir la requete à executer pour afficher les donnés.
                // On peut lui passer le dtFilter pour récuperer les choix de filtres de l'utilisateur
                // il faut également passer le QueryBuilder de la fonction sinon ca ne fonctionne pas.
                // et ne pas oublier de faire le $qb->from(Test::class, 't')
                'query' => function (QueryBuilder $qb) {
                    $qb->from(Person::class, 'p');
                    return $this->testRepository->getQBFindAllStudents($this->dtFiler, $qb);
                }
            ]);
    }
}

```

Exemple de méthode dans le repository

```php
/**
     * Initialisation du queryBuilder pour la recherche d'élèves.
     *
     * Il faut passer le queryBuilder du createAdapter pour que la requete fonctionne sur la liste des élèves.
     * En passsant un DtFilter il est possible d'appliquer les filtres ce celui-ci.
     *
     * @param DtFilter|null $dtFilter
     * @param QueryBuilder|null $qb
     * @return QueryBuilder
     */
    public function getQBFindAllStudents(DtFilter $dtFilter = null, QueryBuilder $qb = null)
    {
        if (!$qb instanceof QueryBuilder) {
            $qb = $this->createQueryBuilder('p');
        }

        $qb
            ->select('p')
            ->addSelect('s')
            ->addSelect('sc')
            ->addSelect('c')
            ->addSelect('division')
            ->addSelect('unit')
            ->addSelect('division.title as classroomTitle , unit.color as unitColor, regime.title as regimeTitle')
            ->join('p.student', 's')
            ->join('s.studentClassrooms', 'sc', 'WITH', 'CURRENT_TIMESTAMP() >= sc.startDate AND CURRENT_TIMESTAMP() <= sc.endDate')
            ->leftJoin('sc.classroom', 'c')
            ->join('s.regime', 'regime')
            ->leftjoin('c.division', 'division')
            ->leftjoin('division.unit', 'unit')
            ->leftjoin('s.responsibleStudents', 'studentResponsible')
            ->leftjoin('studentResponsible.responsible', 'responsibleStudent')
            ->leftjoin('s.responsibleStudents', 'responsibleStudentPrincipal', 'WITH', 'responsibleStudentPrincipal.principal = 1')
            ->leftjoin('responsibleStudentPrincipal.responsible', 'responsiblePrincipal')
            ->where('p.student IS NOT NULL')
            ->groupBy('p.id');

        if ($dtFilter instanceof DtFilter) {
            if ($dtFilter->classroom != null) {
                $qb
                    ->andWhere('s.classroom = :classroomId')
                    ->setParameter('classroomId', $dtFilter->classroom);
            }

            if ($dtFilter->division != null) {
                $qb
                    ->andWhere('c.division = :divisionId')
                    ->setParameter('divisionId', $dtFilter->division);
            }

            if ($dtFilter->RemoveNoClassroom == true) {
                $qb->andWhere('sc IS NULL');
            }
            else {
                $qb->andWhere('c IS NOT NULL');
            }

            if ($dtFilter->regime != null) {
                $qb
                    ->setParameter('regimeId',$dtFilter->regime)
                    ->andWhere('regime.id = :regimeId');
            }

            if ($dtFilter->Prenom != null) {
                $qb
                    ->andWhere('p.firstName LIKE :firstName')
                    ->setParameter('firstName', '%' . $dtFilter->Prenom . '%');
            }
            if ($dtFilter->Nom != null) {
                $qb
                    ->andWhere('p.lastName LIKE :lastName')
                    ->setParameter('lastName', '%' . $dtFilter->Nom . '%');
            }
            if ($dtFilter->unit != null) {
                $qb
                    ->join('c.division', 'd')
                    ->join('d.unit', 'u')
                    ->andWhere('u.id = :unitId')
                    ->setParameter('unitId', $dtFilter->unit);
            }
            if ($dtFilter->brotherhoodSize != null) {
                $qb
                    ->andWhere('responsiblePrincipal.brotherhoodSize = :brotherhoodSize')
                    ->setParameter('brotherhoodSize', $dtFilter->brotherhoodSize);
            }
            if ($dtFilter->brotherhoodRank != null) {
                $qb
                    ->andWhere('s.brotherhoodRank = :brotherhoodRank')
                    ->setParameter('brotherhoodRank', $dtFilter->brotherhoodRank);
            }
        }

        return $qb;
    }
```
### Step 5 :  JS

Exemple dans un fichier search_student.js

Ce fichier JS est charger dans le webpack via un addEntry.

```javascript
import DatatablesConfig from '../../../../bundles/edulogdatatables/js/DatatablesConfig.js';

let dtConfig = new DatatablesConfig('#studentsList');

// initialisation simple et automatique (filtres + affichage du tableau)
//dataTablesConfig.initialize();

// Ajout d'une option JS datatables.
//dataTablesConfig.addOption('paging', true);
//dataTablesConfig.removeOption('responsive');


//initialisation custom
dtConfig.customInitialize();

// Permet d'ajouter une fonction 
dtConfig.addThenCallback(function(dt){
    // méthode utilisé par le intialize (elle se charge de soumettre le formulaire pour sauvegarder
    // les valeurs choisies par l'user et dessine ensuite le tableau.
    // Ici il est possible de faire d'autre traitement JS
    dtConfig.initDefaultThenCallback(dt);
});
````


### Step 6 : Extension twig pour afficher le tableau 

```twig

// Load des fichiers du webpackConfig.

{% block stylesheets %}
    {{ parent() }}
    {{ encore_entry_link_tags('student_search') }}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('student_search') }}
{% endblock %}

// on lui passe en paramètre le datatable (issu du controller), l'id du tableau, et le formulaire de filtre(form)
// si on en a un.

{{ render_datatables(datatable, 'studentsList', form) }}
```

Rappels Yarn

```console

# compile assets once
$ yarn encore dev

# or, recompile assets automatically when files change
$ yarn encore dev --watch
 
# # or, recompile assets automatically when files change and refresh browser automatically
$ yarn encore dev-server --watch --disable-host-check 

# on deploy, create a production build
$ yarn encore production
```

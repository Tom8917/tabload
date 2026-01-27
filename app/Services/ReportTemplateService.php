<?php

namespace App\Services;

use App\Models\ReportModel;
use App\Models\ReportSectionModel;

class ReportTemplateService
{
    public function __construct(
        private readonly ReportSectionModel   $sections,
        private readonly ReportSectionService $sectionService
    )
    {
    }

    public function buildReportSkeleton(int $reportId, array $config): void
    {
        $report = model(ReportModel::class)->find($reportId);
        $appName = trim((string)($report['application_name'] ?? ''));
        $appVersion = trim((string)($report['version'] ?? ''));
        $entFile = trim((string)($report['file_name'] ?? ''));

        if ($appName === '') {
            $appName = '"Non définie"';
        }

        if ($appVersion === '') {
            $appVersion = '"Aucune version"';
        }

        $mediaId = (int)($report['file_media_id'] ?? 0);
        $entFile = '';

        if ($mediaId > 0) {
            $m = model(\App\Models\MediaModel::class)->find($mediaId);
            $entFile = trim((string)($m['file_name'] ?? ''));
        }

        if ($entFile === '') $entFile = 'Aucun document renseigné';

        $ctx = [
            'reportId' => $reportId,
            'appName' => $appName,
            'appVersion' => $appVersion,
            'entFile' => $entFile,
            'tests' => $config['tests'] ?? [],
        ];

        $position = 1;

        // 1
        $synthId = $this->insertSection(
            reportId: $reportId,
            parentId: null,
            position: $position++,
            title: 'Synthèse',
            content: null
        );
        $this->createFromDefinition($reportId, $synthId, $this->defSynthese($ctx), $ctx);

        //2
        $campId = $this->insertSection($reportId, null, $position++, 'Description de la campagne', null);
        $this->createFromDefinition($reportId, $campId, $this->defCampaign($ctx), $ctx);

        // 3
        foreach ($this->enabledTestsInOrder($ctx['tests']) as $type) {
            $cible = '';
            if ($type === 'target') {
                $cible = trim((string)($ctx['tests']['target']['target'] ?? ''));
            }

            $testCtx = $ctx + ['type' => $type, 'cible' => $cible];

            $rootTitle = $this->testRootTitle($type, $cible);
            $testRootId = $this->insertSection($reportId, null, $position++, $rootTitle, null);

            $this->createFromDefinition($reportId, $testRootId, $this->defTestBlock($testCtx), $testCtx);
        }

        // 4
        $conclId = $this->insertSection($reportId, null, $position++, 'Conclusion', null);
        $this->createFromDefinition($reportId, $conclId, $this->defConclusion($ctx), $ctx);

        $this->sectionService->recomputeCodes($reportId);
    }


    private function createFromDefinition(int $reportId, int $parentId, array $definition, array $ctx): void
    {
        $pos = 1;

        foreach ($definition as $node) {
            $title = $this->resolve($node['title'] ?? '', $ctx);
            $content = $this->resolveNullable($node['content'] ?? null, $ctx);

            $id = $this->insertSection(
                reportId: $reportId,
                parentId: $parentId,
                position: $pos++,
                title: $title,
                content: $content
            );

            $children = $node['children'] ?? [];
            if (!empty($children)) {
                $this->createFromDefinition($reportId, $id, $children, $ctx);
            }
        }
    }

    private function insertSection(int $reportId, ?int $parentId, int $position, string $title, ?string $content): int
    {
        $level = $parentId === null ? 1 : 2;

        return (int)$this->sections->insert([
            'report_id' => $reportId,
            'parent_id' => $parentId,
            'position' => $position,
            'level' => $level,
            'code' => null,
            'title' => $title,
            'content' => $content,
        ], true);
    }

    private function resolve(mixed $value, array $ctx): string
    {
        if (is_callable($value)) {
            return (string)$value($ctx);
        }
        return (string)$value;
    }

    private function resolveNullable(mixed $value, array $ctx): ?string
    {
        if ($value === null) return null;
        if (is_callable($value)) return (string)$value($ctx);
        $v = (string)$value;
        return $v === '' ? null : $v;
    }

    private function enabledTestsInOrder(array $tests): array
    {
        $order = ['target', 'endurance', 'limits', 'overload'];
        $out = [];

        foreach ($order as $t) {
            if (!empty($tests[$t]['enabled'])) {
                $out[] = $t;
            }
        }
        return $out;
    }




    // Partie 1 : Synthèse
    private function defSynthese(array $ctx): array
    {
        return [
            [
                'title' => 'Objectif',
                'content' => fn($c) => $this->tplSyntheseObjectif($c['appName']),
            ],
            [
                'title' => 'Éléments notables',
                'content' => fn() => $this->tplPlaceholderList(),
            ],
            [
                'title' => 'Principaux résultats',
                'children' => $this->defSyntheseResultsChildren($ctx),
            ],
            [
                'title' => 'Comparaison avec les campagnes précédentes',
                'content' => fn($c) => $this->tplSyntheseComparaison($c['appName'], $c['appVersion']),
            ],
            [
                'title' => 'Conclusion - Conformité aux exigences',
                'content' => fn($c) => $this->tplSyntheseConformite($c['appName'], $c['appVersion']),
            ],
        ];
    }

    private function defSyntheseResultsChildren(array $ctx): array
    {
        $children = [];
        foreach ($this->enabledTestsInOrder($ctx['tests']) as $type) {
            $children[] = [
                'title' => $this->syntheseResultTitle($type),
                'content' => fn() => $this->tplPlaceholderShort(),
            ];
        }
        return $children;
    }

    private function syntheseResultTitle(string $type): string
    {
        return match ($type) {
            'target' => 'Résultats du test à la cible',
            'endurance' => "Résultats du test d’endurance",
            'limits' => 'Résultats du test aux limites',
            'overload' => 'Résultats du test de surcharge',
            default => 'Résultats du test',
        };
    }






    // Partie 2 : Description de la campagne
    private function defCampaign(array $ctx): array
    {
        return [
            [
                'title' => 'Protocole opératoire',
                'content' => fn() => $this->tplCampaignProtocole(),
                'children' => [
                    ['title' => 'Schéma de l\'architecture', 'content' => fn() => $this->tplPlaceholderList()],
                ],
            ],
            [
                'title' => 'Exigences - Performances attendues',
                'content' => fn($c) => $this->tplCampaignExigences($c['entFile']),
                'children' => [
                    ['title' => 'Scénarios', 'content' => fn() => $this->tplCampaignScenarios()],
                    ['title' => 'Activités', 'content' => fn() => $this->tplCampaignActivites()],
                    ['title' => 'Périodes', 'content' => fn() => $this->tplCampaignPeriode()],
                ],
            ],
            [
                'title' => 'Plan de test',
                'content' => fn() => $this->tplCampaignPlan(),
            ],
        ];
    }



    // Partie 3 jusuq'à max 6 : les différents tests
    private function defTestBlock(array $ctx): array
    {
        return [
            [
                'title' => 'Objectif',
                'content' => fn($c) => $this->tplTestObjectif($c['type'], $c['cible']),
                'children' => [
                    [
                        'title' => 'Rappel des débits utilisés',
                        'content' => fn() => $this->tplPlaceholderShort(),
                    ],
                    [
                        'title' => 'Modèle de Charge',
                        'content' => fn() => $this->tplPlaceholderShort(),
                    ],
                    [
                        'title' => 'Condition d\'exécution',
                        'content' => fn() => $this->tplPlaceholderShort(),
                    ],
                ],
            ],
            [
                'title' => 'Résultats',
                'content' => fn() => $this->tplTestResultsIntro(),
                'children' => [
                    [
                        'title' => 'Débits transactionnels',
                        'content' => fn() => $this->tplPlaceholderShort(),
                        'children' => [
                            ['title' => 'Graphique des débits transactionnels', 'content' => fn() => $this->tplPlaceholderShort()],
                        ],
                    ],
                    [
                        'title' => 'Temps de réponse',
                        'content' => fn() => $this->tplPlaceholderShort(),
                        'children' => [
                            ['title' => 'Tableau des temps de réponse', 'content' => fn() => $this->tplPlaceholderShort()],
                            ['title' => 'Analyse', 'content' => fn() => $this->tplPlaceholderShort()],
                            ['title' => 'Graphique des temps de réponse', 'content' => fn() => $this->tplPlaceholderShort()],
                        ],
                    ],
                    [
                        'title' => 'Tableau des erreurs',
                        'content' => fn() => $this->tplPlaceholderShort(),
                    ],
                    [
                        'title' => 'Monitoring',
                        'content' => fn() => $this->tplPlaceholderList(),
                        'children' => [
                            ['title' => 'CPU disponible sur les serveurs d\'application', 'content' => fn() => $this->tplPlaceholderShort()],
                            ['title' => 'CPU disponible sur les serveurs de base de données', 'content' => fn() => $this->tplPlaceholderShort()],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Conformité',
                'content' => fn() => $this->tplTestConformite(),
            ],
        ];
    }




    // Dernière partie : Conclusion (inexistante encore)
    private function defConclusion(array $ctx): array
    {
        return [
            [
                'title' => 'Synthèse finale',
                'content' => fn() => $this->tplPlaceholderShort(),
            ],
            [
                'title' => 'Recommandations prioritaires',
                'content' => fn() => $this->tplPlaceholderList(),
            ],
            [
                'title' => 'Prochaines étapes',
                'content' => fn() => $this->tplPlaceholderList(),
            ],
        ];
    }



    // Les différents types de tests
    private function testRootTitle(string $type, string $cible): string
    {
        return match ($type) {
            'target' => 'Test à la cible' . ($cible !== '' ? ' — ' . $cible : ''),
            'endurance' => "Test d'endurance",
            'limits' => "Test aux limites",
            'overload' => "Test de surcharge",
            default => "Test",
        };
    }






    // Objectif
    private function tplSyntheseObjectif(string $appName): string
    {
        $appName = esc($appName);

        return <<<HTML
<p>L'objectif de la campagne est de vérifier que l’application {$appName} satisfait aux exigences 
en termes de débit transactionnel et aux critères d'écceptablitié 
en termes de temps de réponse et de taux d'erreurs.</p>
HTML;
    }


    private function tplSyntheseComparaison(string $appName, string $appVersion): string
    {
        $appName = esc($appName);
        $appVersion = esc($appVersion);

        return <<<HTML
<p><em>Description rapide des différences à débit / exigences égaux</em></p>
<br><br>
Notes :<br>
<ul>
   <li>Le pourcentage de variation est caluclé de la façon suivante : &Delta; = ((Vn - Vn-1)/Vn-1)*100</li> 
   <li>Les temps affichés étant des T95, leur somme peut différer du T95 global.</li>
</ul>
HTML;
    }


    private function tplSyntheseConformite(string $appName, string $appVersion): string
    {
        $appName = esc($appName);
        $appVersion = esc($appVersion);

        return <<<HTML
<div class="card gap-2">
<p>L'application {$appName} en version {$appVersion}</p><br>
<ul>
<li> est conforme à l'exigence de charge en termes de débit transactionnel pour la période PX;</li>
<li>respecte les critères d'acceptabilité fonctionnels de temps de réponse;</li>
<li>respecte les critères d'acceptabilité fonctionnels de taux d'erreur;</li>
<li>respecte les critères d'acceptabilité techniques de consommation CPU;</li>
<li>respecte les critères d'acceptabilité techniques de consommation mémoire;</li>
</ul>
<br>
De plus:<br>
<ul>
<li>l'application est stable durant les XXh de tir d'endurance.</li>
<li>la limite est au-delà des XXX transactions par seconde en période PX.</li>
<li>l'application est résiliente à un pic d'utilisation poncutel de XX %.</li>
</ul>
</div>
HTML;
    }









    // Description de la campagne
    private function tplCampaignProtocole(): string
    {
        return <<<HTML
<p>Les temps de réponse sont obtenus aux pieds des serveurs, 
hors temps réseau jusqu'à l'utilisateur final, 
et hors temps de génération de l'affichage sur le poste client.</p>
HTML;
    }

    private function tplCampaignExigences(string $entFile): string
    {
        $entFile = esc($entFile);

        return <<<HTML
<p>Les exigences sont formulées dans le document "{$entFile}".</p>

<p>Une campagne de tests de performance consiste à exécuter des tests sur des Périodes 
(qui modélisent des Périodes réelles du plan de production).</p>

<p>Une période est composée d'Activités qui sont-elles même composées de Scénarios. 
À chaque Période est associée une exigence de charge.</p>

<p>Les tableaux ci-après décrivent les critères d'acceptablité de temps de réponse et techniques qui ont été utilisés au cours de la campagne.<br>
"insérer les tableaux".</p>

<p>Les tableaux ci-après décrivent les Scénarios, Activités et Périodes utilisés dans cette campagne ainsi que les exigences de charge associées.</p>
HTML;
    }

    
    private function tplCampaignScenarios(): string
    {
        return <<<HTML
<p>Les scénarios utilisateurs ont été scindés en étapes appelées "Points de mesure". 
De plus à chaque scénario est associé un point de mesure implicite - qui représente la totalité du scénario - nommé "Durée transaction".<br>
"insérer le tableau de description de scénarios".</p>
HTML;
    }

    private function tplCampaignActivites(): string
    {
        return <<<HTML
<p>Les scénarios utilisateurs ont été scindés en étapes appelées "Points de mesure". 
De plus à chaque scénario est associé un point de mesure implicite - qui représente la totalité du scénario - nommé "Durée transaction".<br>
"insérer le tableau des activités".</p>
HTML;
    }

    private function tplCampaignPeriode(): string
    {
        return <<<HTML
<p>Les scénarios utilisateurs ont été scindés en étapes appelées "Points de mesure". 
De plus à chaque scénario est associé un point de mesure implicite - qui représente la totalité du scénario - nommé "Durée transaction".<br>
"insérer le tableau des périodes".</p>
HTML;
    }

    private function tplCampaignPlan(): string
    {
        return <<<HTML
<p>Le tableau ci-après décrit le plan de test demandé par la Maîtrise d'Ouvrage :<br>
"insérer le tableau".</p>
HTML;
    }







    //Tests
    private function tplTestObjectif(string $type, string $cible): string
    {
        $label = match ($type) {
            'target' => "test à la cible" . ($cible !== '' ? " (" . esc($cible) . ")" : ''),
            'endurance' => "test d’endurance",
            'limits' => "test aux limites",
            'overload' => "test de surcharge",
            default => "test",
        };

        return <<<HTML
<p>L'objectif de ce {$label} est de vérifier que lorsque le système est soumis à une sollicitation conforme aux exigences de XXX transactions par seconde, 
les critères d'acceptabilité fonctionnels et techniques formulés par la Maîtrise d'Ouvrage sont respectés.</p>
HTML;
    }

    private function tplTestResultsIntro(): string
    {
        return <<<HTML
HTML;
    }

    private function tplTestConformite(): string
    {
        return <<<HTML
<p>S'il n'est pas conforme, préciser pourquoi.</p>
HTML;
    }







    // Champs vides
    private function tplPlaceholderShort(): string
    {
        return <<<HTML
<p>À compléter.</p>
HTML;
    }

    private function tplPlaceholderList(): string
    {
        return <<<HTML
<p>Points à compléter.</p>
<ul>
    <li>...</li>
    <li>...</li>
</ul>
HTML;
    }
}

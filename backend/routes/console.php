<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tâches planifiées
|--------------------------------------------------------------------------
|
| Tâches de l'architecture (§3.5), ajoutées avec leurs modules.
|
*/

/*
 * Purge des données au-delà de leur durée de conservation (config/wariss.php, « retention ») :
 * messages de contact (24 mois) ; journal d'activité et analytics bruts avec leurs modules.
 */
Schedule::command('model:prune')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();

/*
 * Publication des contenus programmés dont la date est atteinte.
 */
Schedule::command('content:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

/*
 * Agrégats quotidiens de la mesure d'audience (D17) pour la veille, avant la purge de 03:00.
 */
Schedule::command('analytics:aggregate')
    ->dailyAt('00:15')
    ->withoutOverlapping()
    ->onOneServer();

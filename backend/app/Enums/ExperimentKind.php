<?php

namespace App\Enums;

/**
 * Nature d'une expérimentation du laboratoire (domaine D11).
 * Valeurs reprises dans la contrainte CHECK experiments_kind_check.
 */
enum ExperimentKind: string
{
    case Demo = 'demo';
    case Prototype = 'prototype';
    case Tool = 'tool';
    case ApiTest = 'api_test';
    case Visualization = 'visualization';
    case UiExperiment = 'ui_experiment';
    case Learning = 'learning';
}

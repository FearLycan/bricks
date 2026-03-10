<?php

use common\enums\image\StatusEnum;
use common\models\Set;
use common\models\Theme;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array<int, array{label:string, value:int, hint:string}> $stats */
/** @var Set[] $recentSets */
/** @var Theme[] $topThemes */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admin-dashboard">
    <div class="d-flex align-items-center gap-2 mb-4">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary">Quick overview of catalog quality and recent activity.</div>
        </div>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('Manage sets', ['/admin/set/index'], ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ($stats as $stat): ?>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-body-secondary small text-uppercase mb-2"><?= Html::encode($stat['label']) ?></div>
                        <div class="display-6 fw-semibold mb-1"><?= Html::encode((string)$stat['value']) ?></div>
                        <div class="small text-body-secondary"><?= Html::encode($stat['hint']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h5 mb-0">Recently added sets</h2>
                        <?= Html::a('View all', ['/admin/set/index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                    </div>

                    <?php if ($recentSets !== []): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Name</th>
                                    <th>Theme</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentSets as $set): ?>
                                    <tr>
                                        <td><?= Html::encode($set->number ?: '-') ?></td>
                                        <td><?= Html::encode($set->name ?: '-') ?></td>
                                        <td><?= Html::encode($set->theme->name ?? '-') ?></td>
                                        <td>
                                            <?php $status = StatusEnum::tryFrom((int)$set->status)?->label() ?? '-'; ?>
                                            <span class="badge rounded-pill text-bg-light border text-dark"><?= Html::encode($status) ?></span>
                                        </td>
                                        <td class="text-end">
                                            <?= Html::a('Open', ['/admin/set/view', 'id' => $set->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-body-secondary mb-0">No sets available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Top themes</h2>
                    <?php if ($topThemes !== []): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($topThemes as $theme): ?>
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?= Html::encode($theme->name ?? '-') ?></div>
                                        <div class="small text-body-secondary">
                                            <?= Html::encode((string)($theme->year_from ?? '-')) ?> - <?= Html::encode((string)($theme->year_to ?? '-')) ?>
                                        </div>
                                    </div>
                                    <span class="badge text-bg-light border text-dark"><?= Html::encode((string)($theme->sets_count ?? 0)) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-body-secondary mb-0">No themes available yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h5 mb-3">Suggested next steps</h2>
                    <ul class="mb-0 ps-3">
                        <li>Review inactive sets and complete missing data.</li>
                        <li>Add images for sets that still do not have media.</li>
                        <li>Open recent entries and verify theme, status, and price values.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

use common\models\SearchWizardHash;
use yii\helpers\Json;

?>
<script>window.WizardConfig = <?= Json::encode(SearchWizardHash::getJsConfig()) ?>;</script>
<div class="modal fade" id="wizardModal" tabindex="-1" aria-labelledby="wizardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content wizard-modal-content">

            <div class="modal-header wizard-modal-header border-0 pb-2">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-magic text-primary"></i>
                            <span class="fw-semibold" style="font-size:0.95rem;">LEGO Set Finder</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="wizard-step-indicator text-muted small" id="wizardStepIndicator"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="progress wizard-progress-bar">
                        <div class="progress-bar" id="wizardProgressBar" role="progressbar" style="width:0%"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            <div class="modal-body wizard-modal-body" id="wizardBody">
            </div>

            <div class="modal-footer wizard-modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary wizard-btn-back" id="wizardBackBtn" hidden>
                    <i class="bi bi-arrow-left me-1"></i>Back
                </button>
                <button type="button" class="btn btn-primary ms-auto wizard-btn-next" id="wizardNextBtn" disabled>
                    Next<i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>

        </div>
    </div>
</div>

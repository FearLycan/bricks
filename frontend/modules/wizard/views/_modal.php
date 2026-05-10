<?php

use common\models\SearchWizardHash;
use yii\helpers\Json;

?>
<script>window.WizardConfig = <?= Json::encode(SearchWizardHash::getJsConfig()) ?>;</script>
<div class="modal fade" id="wizardModal" tabindex="-1" aria-labelledby="wizardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content wizard-modal-content">

            <div class="modal-header wizard-modal-header border-0 pb-0">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-magic wizard-header-icon"></i>
                            <span class="wizard-header-title">LEGO Set Finder</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="wizard-step-indicator" id="wizardStepIndicator"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="wizard-dots" id="wizardDots"></div>
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

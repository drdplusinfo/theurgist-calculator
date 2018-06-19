<?php
namespace DrdPlus\TheurgistCalculator\Formulas;

use DrdPlus\Theurgist\Codes\FormulaMutableSpellParameterCode;
use DrdPlus\Theurgist\Spells\SpellParameters\Partials\CastingParameter;
use Granam\String\StringTools;

/** @var FormulasController $controller */

$formulasTable = $controller->getFormulasTable();
$currentFormula = $controller->getCurrentFormula();
$currentFormulaCode = $currentFormula->getFormulaCode();

$formulaParametersWithoutUnit = [
    FormulaMutableSpellParameterCode::ATTACK,
    FormulaMutableSpellParameterCode::BRIGHTNESS,
    FormulaMutableSpellParameterCode::POWER,
    FormulaMutableSpellParameterCode::DETAIL_LEVEL,
    FormulaMutableSpellParameterCode::SIZE_CHANGE,
];
foreach ($formulaParametersWithoutUnit as $parameterName) {
    $getParameter = StringTools::assembleGetterForName($parameterName);
    /** @var CastingParameter $parameter */
    $parameter = $formulasTable->$getParameter($currentFormulaCode);
    if ($parameter === null) {
        continue;
    }
    $parameterCode = FormulaMutableSpellParameterCode::getIt($parameterName);
    ?>
  <div class="parameter panel">
    <label><?= $parameterCode->translateTo('cs') ?>:
        <?php
        $parameterAdditionByDifficulty = $parameter->getAdditionByDifficulty();
        $additionStep = $parameterAdditionByDifficulty->getAdditionStep();
        $optionParameterValue = $parameter->getDefaultValue(); // from the lowest
        $parameterDifficultyChange = $parameterAdditionByDifficulty->getCurrentDifficultyIncrement();
        $optionParameterChange = 0;
        $previousOptionParameterValue = null;
        $selectedParameterValue = $controller->getCurrentFormulaSpellParameters()[$parameterName] ?? false;
        ?>
      <select name="<?= FormulasController::FORMULA_PARAMETERS ?>[<?= $parameterName ?>]">
          <?php
          do {
              if ($previousOptionParameterValue === null || $previousOptionParameterValue < $optionParameterValue) { ?>
                <option value="<?= $optionParameterValue ?>"
                        <?php if ($selectedParameterValue !== false && $selectedParameterValue === $optionParameterValue){ ?>selected<?php } ?>>
                    <?= ($optionParameterValue >= 0 ? '+' : '')
                    . "{$optionParameterValue} [{$parameterDifficultyChange}]"; ?>
                </option>
              <?php }
              $previousOptionParameterValue = $optionParameterValue;
              $optionParameterValue++;
              $optionParameterChange++;
              /** @noinspection PhpUnhandledExceptionInspection */
              $parameter = $parameter->getWithAddition($optionParameterChange);
              $parameterAdditionByDifficulty = $parameter->getAdditionByDifficulty();
              $parameterDifficultyChange = $parameterAdditionByDifficulty->getCurrentDifficultyIncrement();
          } while ($additionStep > 0 /* at least once even on no addition possible */ && $parameterDifficultyChange < 21) ?>
      </select>
    </label>
  </div>
<?php } ?>
<?php
/**
 * ฟอร์มชื่อและคำนำหน้า — คำนำหน้าหลักที่ personnel (ถ้าเชื่อมและมีคอลัมน์) ไม่มีจึงใช้ user.title
 *
 * @var array|null $account_user แถว user ของบัญชีที่ล็อกอิน
 * @var array|null $person        แถว personnel (+ join user) หรือ null
 * @var string     $id_prefix     prefix สำหรับ id ฟอร์ม (เช่น profile, cv-identity)
 */
$acc = $account_user ?? null;
if ($acc === null) {
    return;
}
$idPx = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($id_prefix ?? 'profile'));
if ($idPx === '') {
    $idPx = 'profile';
}
$pmRow = $person ?? null;
?>
<form action="<?= base_url('dashboard/profile/identity') ?>" method="post" class="space-y-4">
    <?= csrf_field() ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php
        $titleOptions = \App\Libraries\CvProfile::academicTitleOptionsTh();
        $pAc = $pmRow !== null ? trim((string) ($pmRow['academic_title'] ?? '')) : '';
        $uAc = trim((string) ($acc['title'] ?? ''));
        $curTitle = old('title', $pAc !== '' ? $pAc : $uAc);
        if ($curTitle !== '' && ! array_key_exists($curTitle, $titleOptions)) {
            $titleOptions = [$curTitle => $curTitle . ' (ค่าที่บันทึกไว้)'] + $titleOptions;
        }
        $titleEnOptions = \App\Libraries\CvProfile::academicTitleOptionsEn();
        $pAcEn = $pmRow !== null ? trim((string) ($pmRow['academic_title_en'] ?? '')) : '';
        $curTitleEn = old('academic_title_en', $pAcEn);
        if ($curTitleEn === '' && $pAcEn === '' && $pmRow !== null && $uAc !== '') {
            $curTitleEn = \App\Libraries\CvProfile::mapAcademicTitleThToEn($uAc);
        }
        if ($curTitleEn !== '' && ! array_key_exists($curTitleEn, $titleEnOptions)) {
            $titleEnOptions = [$curTitleEn => $curTitleEn . ' (ค่าที่บันทึกไว้)'] + $titleEnOptions;
        }
        ?>
        <div class="sm:col-span-2">
            <label for="<?= esc($idPx, 'attr') ?>-title" class="block text-sm font-medium text-gray-700 mb-1">คำนำหน้าชื่อ (ไทย)</label>
            <select name="title" id="<?= esc($idPx, 'attr') ?>-title"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                <?php foreach ($titleOptions as $tVal => $tLabel): ?>
                    <option value="<?= esc($tVal, 'attr') ?>" <?= ($curTitle === $tVal) ? 'selected' : '' ?>><?= esc($tLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="sm:col-span-2">
            <label for="<?= esc($idPx, 'attr') ?>-title-en" class="block text-sm font-medium text-gray-700 mb-1">คำนำหน้าชื่อ (English) — บันทึกใน personnel เมื่อเชื่อมบุคลากร</label>
            <select name="academic_title_en" id="<?= esc($idPx, 'attr') ?>-title-en"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                <?php foreach ($titleEnOptions as $tVal => $tLabel): ?>
                    <option value="<?= esc($tVal, 'attr') ?>" <?= ($curTitleEn === $tVal) ? 'selected' : '' ?>><?= esc($tLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="<?= esc($idPx, 'attr') ?>-tf" class="block text-sm font-medium text-gray-700 mb-1">ชื่อ (ภาษาไทย)</label>
            <input type="text" name="tf_name" id="<?= esc($idPx, 'attr') ?>-tf" maxlength="255"
                   value="<?= esc(old('tf_name', $pmRow['user_tf_name'] ?? $acc['tf_name'] ?? '')) ?>"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                   autocomplete="given-name" lang="th">
        </div>
        <div>
            <label for="<?= esc($idPx, 'attr') ?>-tl" class="block text-sm font-medium text-gray-700 mb-1">นามสกุล (ภาษาไทย)</label>
            <input type="text" name="tl_name" id="<?= esc($idPx, 'attr') ?>-tl" maxlength="255"
                   value="<?= esc(old('tl_name', $pmRow['user_tl_name'] ?? $acc['tl_name'] ?? '')) ?>"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                   autocomplete="family-name" lang="th">
        </div>
        <div>
            <label for="<?= esc($idPx, 'attr') ?>-gf" class="block text-sm font-medium text-gray-700 mb-1">ชื่อ (ภาษาอังกฤษ)</label>
            <input type="text" name="gf_name" id="<?= esc($idPx, 'attr') ?>-gf" maxlength="255"
                   value="<?= esc(old('gf_name', $pmRow['user_gf_name'] ?? $acc['gf_name'] ?? '')) ?>"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                   autocomplete="given-name" lang="en">
        </div>
        <div>
            <label for="<?= esc($idPx, 'attr') ?>-gl" class="block text-sm font-medium text-gray-700 mb-1">นามสกุล (ภาษาอังกฤษ)</label>
            <input type="text" name="gl_name" id="<?= esc($idPx, 'attr') ?>-gl" maxlength="255"
                   value="<?= esc(old('gl_name', $pmRow['user_gl_name'] ?? $acc['gl_name'] ?? '')) ?>"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                   autocomplete="family-name" lang="en">
        </div>
    </div>
    <p class="text-xs text-gray-500">ต้องกรอกชื่อ-นามสกุลอย่างน้อยหนึ่งภาษา คำนำหน้าเป็นทางเลือก</p>
    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-yellow-500 hover:bg-yellow-600 text-gray-900 text-sm font-semibold transition-colors">
        บันทึกชื่อและคำนำหน้า
    </button>
</form>

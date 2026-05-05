<?php

namespace App\Libraries;

/**
 * รายการตำแหน่งในโครงสร้างองค์กร (ไทย) — ใช้ร่วมกันระหว่างแอดมินและกฎ personnel_org_roles
 */
class OrganizationPositionCatalog
{
    /**
     * @return array<string, array<string, string>> group => [ value => label ]
     */
    public static function getGroupedOptions(): array
    {
        $researchOptions = [
            'หัวหน้าหน่วยการจัดการงานวิจัย' => 'หัวหน้าหน่วยการจัดการงานวิจัย',
            'กรรมการหน่วยจัดการงานวิจัย' => 'กรรมการหน่วยจัดการงานวิจัย',
        ];
        foreach (OrganizationResearchPositionExtras::getAll() as $extra) {
            $researchOptions[$extra] = $extra;
        }

        return [
            'บริหาร' => [
                'คณบดี' => 'คณบดี',
                'รองคณบดี' => 'รองคณบดี',
                'ผู้ช่วยคณบดี' => 'ผู้ช่วยคณบดี',
            ],
            'หน่วยงานวิจัย' => $researchOptions,
            'สำนักงานคณบดี' => [
                'หัวหน้าสำนักงานคณบดี' => 'หัวหน้าสำนักงานคณบดี',
                'เจ้าหน้าที่' => 'เจ้าหน้าที่',
            ],
            'หลักสูตร' => [
                'อาจารย์' => 'อาจารย์',
                'ประธานหลักสูตร' => 'ประธานหลักสูตร',
                'อาจารย์ประจำหลักสูตร' => 'อาจารย์ประจำหลักสูตร',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function getAllowedTitles(): array
    {
        $keys = [];
        foreach (self::getGroupedOptions() as $opts) {
            foreach (array_keys($opts) as $value) {
                $keys[] = $value;
            }
        }

        return array_values(array_unique($keys));
    }
}

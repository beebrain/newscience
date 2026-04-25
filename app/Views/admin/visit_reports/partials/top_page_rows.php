<?php if (empty($rows)): ?>
    <tr><td colspan="5" style="color: var(--color-gray-600);">ยังไม่มีข้อมูล</td></tr>
<?php else: ?>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= esc($row['title']) ?></td>
            <td><code><?= esc($row['route']) ?></code></td>
            <td><?= esc($row['content_type']) ?></td>
            <td style="text-align: right;"><?= number_format((int) $row['views']) ?></td>
            <td style="text-align: right;"><?= number_format((int) $row['unique_visitors']) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

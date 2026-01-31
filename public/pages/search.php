<?php
// Search page with filters + pagination (same style as members)

global $db;

$per_page = 20;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Filters
$gender = $_GET['gender'] ?? 'all';   // all|m|f
$photo  = $_GET['photo'] ?? 'any';    // any|with|without
$minAge = isset($_GET['minAge']) ? (int)$_GET['minAge'] : 18;
$maxAge = isset($_GET['maxAge']) ? (int)$_GET['maxAge'] : 60;

// Normalize / clamp
if (!in_array($gender, ['all', 'm', 'f'], true)) $gender = 'all';
if (!in_array($photo, ['any', 'with', 'without'], true)) $photo = 'any';
$minAge = max(18, min(99, $minAge));
$maxAge = max(18, min(99, $maxAge));
if ($maxAge < $minAge) $maxAge = $minAge;

// Build WHERE
$where = [];
$where[] = "m.is_approved = 1";

// gender
if ($gender === 'm' || $gender === 'f') {
    $genderEsc = $db->escape($gender);
    $where[] = "m.gender = '{$genderEsc}'";
}

// photo: with/without means thumbnail or image is present
$hasPhotoExpr = "(
    (m.profile_thumbnail IS NOT NULL AND m.profile_thumbnail <> '')
 OR (m.profile_image IS NOT NULL AND m.profile_image <> '')
)";

if ($photo === 'with') {
    $where[] = $hasPhotoExpr;
} elseif ($photo === 'without') {
    $where[] = "NOT {$hasPhotoExpr}";
}

// age range via birthday
// Excludes NULL birthdays to make the filter meaningful
$where[] = "m.birthday IS NOT NULL";
$where[] = "m.birthday <= DATE_SUB(CURDATE(), INTERVAL {$minAge} YEAR)";
$where[] = "m.birthday >= DATE_SUB(CURDATE(), INTERVAL {$maxAge} YEAR)";

$whereSql = count($where) ? ("WHERE " . implode(" AND ", $where)) : "";

// Count
$total_members = (int)$db->get_var("
    SELECT COUNT(*) 
    FROM cz_members m
    {$whereSql}
");

$total_pages = (int)ceil($total_members / $per_page);

// Data
$members = $db->get_results("
    SELECT
      m.id,
      m.birthday,
      m.title,
      m.gender,
      m.wechat,
      m.phone,
      m.email,
      m.description,
      m.profile_image,
      m.profile_thumbnail
    FROM cz_members m
    {$whereSql}
    ORDER BY m.id DESC
    LIMIT {$per_page} OFFSET {$offset}
");

// Meta
$meta_title = '搜索会员｜纽约华人交友｜法拉盛相亲｜筛选男女/年龄/照片';
$meta_description = '搜索纽约华人交友平台会员，可按男女、年龄范围、是否有照片筛选，快速找到合适对象。';
$meta_keywords = '纽约华人交友, 法拉盛相亲, 搜索会员, 纽约找男朋友, 纽约找女朋友, 有照片, 年龄筛选, NYC dating';

include ROOT_PATH . '/templates/header.php';

// Helper: build querystring for pagination while keeping filters
function build_search_url(int $page, string $gender, string $photo, int $minAge, int $maxAge): string {
    $qs = http_build_query([
        'page' => $page,
        'gender' => $gender,
        'photo' => $photo,
        'minAge' => $minAge,
        'maxAge' => $maxAge,
    ]);
    return SITE_URL . '/search?' . $qs;
}
?>

<div class="container">
    <div class="members-section">
        <div class="section-header">
            <h2 class="section-title">搜索会员</h2>
            <?php if( $total_members > 0 ): ?>
                <span class="member-count">共 <?php echo $total_members; ?> 位会员</span>
            <?php endif; ?>
        </div>

        <?php include __DIR__ . '/../templates/search-filters.php'; ?>

        <?php if( empty($members) ): ?>
            <div class="empty-state">
                <p>暂无符合条件的会员</p>
            </div>
        <?php else: ?>
            <div class="members-grid">
                <?php foreach( $members as $member ): ?>
                    <article class="member-card">
                        <a href="<?php echo get_member_url($member->id); ?>" class="member-image-link">
                            <img src="<?php echo get_profile_image_url($member); ?>"
                                 alt="<?php echo htmlspecialchars($member->title); ?>"
                                 class="member-image">
                        </a>
                        <div class="member-card-content">
                            <h3 class="member-name">
                                <a href="<?php echo get_member_url($member->id); ?>">
                                    <?php echo htmlspecialchars($member->title); ?>
                                </a>
                            </h3>
                            <div class="member-info">
                                <?php
                                $age = calculate_age($member->birthday);
                                if( $age ) echo '<span class="age">' . $age . '岁</span>';
                                if( isset($member->gender) && $member->gender ) echo '<span class="gender">' . get_gender_display($member->gender) . '</span>';
                                ?>
                            </div>

                            <?php if( !empty($member->description) ): ?>
                                <p class="member-intro">
                                    <?php echo htmlspecialchars(truncate_text($member->description, 100)); ?>
                                </p>
                            <?php endif; ?>

                            <?php
                            $wechat = isset($member->wechat) && !empty($member->wechat) ? $member->wechat : '';
                            $email = isset($member->email) && !empty($member->email) ? $member->email : '';
                            $phone = isset($member->phone) && !empty($member->phone) ? $member->phone : '';
                            ?>

                            <?php if( $wechat || $email || $phone ): ?>
                                <div class="member-contact">
                                    <?php if( $wechat ): ?>
                                        微信:
                                        <strong class="copy-text" onclick="copy('<?php echo htmlspecialchars($wechat, ENT_QUOTES); ?>')">
                                            <?php echo htmlspecialchars($wechat); ?>
                                        </strong><br/>
                                    <?php endif; ?>

                                    <?php if( $email ): ?>
                                        电邮:
                                        <span class="copy-text" onclick="copy('<?php echo htmlspecialchars($email, ENT_QUOTES); ?>')">
                                            <?php echo htmlspecialchars($email); ?>
                                        </span><br/>
                                    <?php endif; ?>

                                    <?php if( $phone ): ?>
                                        手机:
                                        <span class="copy-text" onclick="copy('<?php echo htmlspecialchars($phone, ENT_QUOTES); ?>')">
                                            <?php echo htmlspecialchars($phone); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <div class="clear"></div>
            </div>

            <?php if( $total_pages > 1 ): ?>
                <div class="pagination">
                    <?php if( $current_page > 1 ): ?>
                        <a href="<?php echo build_search_url($current_page - 1, $gender, $photo, $minAge, $maxAge); ?>"
                           class="pagination-link prev">上一页</a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);

                    if( $start_page > 1 ): ?>
                        <a href="<?php echo build_search_url(1, $gender, $photo, $minAge, $maxAge); ?>"
                           class="pagination-link">1</a>
                        <?php if( $start_page > 2 ): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for( $i = $start_page; $i <= $end_page; $i++ ): ?>
                        <?php if( $i == $current_page ): ?>
                            <span class="pagination-link current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo build_search_url($i, $gender, $photo, $minAge, $maxAge); ?>"
                               class="pagination-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if( $end_page < $total_pages ): ?>
                        <?php if( $end_page < $total_pages - 1 ): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                        <a href="<?php echo build_search_url($total_pages, $gender, $photo, $minAge, $maxAge); ?>"
                           class="pagination-link"><?php echo $total_pages; ?></a>
                    <?php endif; ?>

                    <?php if( $current_page < $total_pages ): ?>
                        <a href="<?php echo build_search_url($current_page + 1, $gender, $photo, $minAge, $maxAge); ?>"
                           class="pagination-link next">下一页</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>

<?php
/**
 * Contact Page
 * Simple contact information page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'تواصل معنا — ' . SITE_NAME;

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="text-center mb-5">
                <h1> تواصل معنا 📧</h1>
                <p class="lead text-muted">
                    نسعد بتواصلكم واستفساراتكم
                </p>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">عن المنصة</h4>
                    <p>
                        <strong><?= SITE_NAME ?></strong> هي منصة رقمية مجانية لإنشاء صفحات تذكارية للمتوفين.
                        نهدف إلى توفير وسيلة سهلة للأهل والأصدقاء للدعاء والذكر والقرآن لمن فارقونا.
                    </p>
                    <p>
                        كل صفحة تذكارية تحتوي على أدعية، قرآن، تسبيح إلكتروني، وأذكار يمكن للجميع المشاركة فيها.
                        الصفحات تبقى دائماً، والأجر يستمر بإذن الله.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                     <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">للاستفسارات والدعم</h4>
                    <p>
                        إذا كان لديك أي استفسار أو اقتراح أو مشكلة تقنية، يمكنك التواصل معنا عبر:
                    </p>

                    <div class="d-grid gap-3 mb-3">
                        <a href="mailto:<?= SUPPORT_EMAIL ?>" class="btn btn-outline-primary">
                            📧 البريد الإلكتروني
                        </a>
                    </div>

                    <p class="mb-0 small text-muted">
                        قبل مراسلتنا، يمكنك الاطلاع على صفحة 
                        <a href="<?= site_url('faq') ?>">الأسئلة الشائعة</a> 
                        فقد تجد فيها إجابة سريعة لاستفسارك.
                    </p>
                </div>
            </div>

            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">تطبيقاتنا الإسلامية</h4>
                 <p>صُممت خصيصًا لدعمك في طلب العلم، وذكر الله، والدعاء، لتجعل رحلتك الروحية أكثر ثراءً وفائدة.</p>

                    <div class="row g-4">
                        <!-- تطبيق مكتبتي -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5>📱 تطبيق مكتبتي</h5>
                                    <p>
                                        هو تطبيق إسلامي مميز يضم قصص الأنبياء بأسلوب بسيط ومشوق، مناسب لكل الأعمار.
                                        يحتوي على أذكار، أدعية، وميزات كثيرة، ويعمل بدون إنترنت لتكون الفائدة دائمًا في
                                        متناولك.
                                    </p>
                                </div>
                                <div>
                                    <a href="<?= APP_MAKTBTI ?>" target="_blank" class="btn btn-primary w-100 mb-2">
                                        تحميل تطبيق مكتبتي
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- تطبيق مكتبتي بلس -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5>📱 مكتبتي بلس</h5>
                                    <p>
                                        النسخة المتقدمة من تطبيق مكتبتي، مكتبة شاملة لكل مسلم، مصمم للمسلمين ذاتيا أفضل
                                        من أي وقت مضى، يحتوي على كل ما يحتاجه المسلم يوميا.
                                    </p>
                                </div>
                                <div>
                                    <a href="<?= APP_MAKTBTI_PLUS ?>" target="_blank"
                                        class="btn btn-primary w-100 mb-2">
                                        تحميل مكتبتي بلس
                                    </a>
                                    <a href="<?= APP_MAKTBTI_PLUS_IOS ?>" target="_blank"
                                        class="btn btn-primary w-100 mb-2">
                                        تحميل للآيفون
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
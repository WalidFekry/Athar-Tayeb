<?php
/**
 * FAQ Page
 * Common questions about Athar Tayeb
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'الأسئلة الشائعة حول أثر طيب ❓ — ' . SITE_NAME;
$pageDescription = 'إجابات عن أكثر الأسئلة شيوعًا حول استخدام منصة أثر طيب وإنشاء الصفحات التذكارية.';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="fw-bold">الأسئلة الشائعة حول أثر طيب ❓</h1>
                <p class="lead text-muted">
                    هنا تجد إجابات مبسّطة عن أكثر الأسئلة التي قد تخطر في بالك قبل أو بعد إنشاء صفحة تذكارية.
                </p>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">هل الخدمة مجانية؟</h2>
                    <p class="text-muted mb-0">
                        نعم، جميع خدمات المنصة مجانية تمامًا، والموقع خيري غير ربحي.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">هل أحتاج إلى إنشاء حساب لاستخدام الموقع؟</h2>
                    <p class="text-muted mb-0">
                        لا، يمكنك إنشاء صفحة تذكارية واستخدام الموقع بالكامل بدون تسجيل حساب. بعد إنشاء الصفحة ستحصل
                        على رابط خاص للتعديل والادارة، احتفظ به في مكان آمن.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">هل أستطيع تعديل الصفحة بعد إنشائها؟</h2>
                    <p class="text-muted mb-0">
                        نعم، من خلال رابط التعديل الخاص الذي يصلك بعد إنشاء الصفحة، يمكنك تعديل البيانات، تحديث الصورة، أو
                        حذف الصفحة نهائيًا.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">ماذا لو فقدت رابط التعديل؟</h2>
                    <p class="text-muted mb-0">
                        حفاظًا على الخصوصية والأمان، لا يمكن استرجاع رابط التعديل بسهولة. ننصح بحفظه في مكان آمن (ملاحظات
                        الجوال مثلًا). عند الضرورة القصوى يمكنك التواصل مع الدعم من صفحة "تواصل معنا".
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">هل صفحات المتوفين تظهر في البحث العام؟</h2>
                    <p class="text-muted mb-0">
                        نعم، الصفحات المعتمدة تظهر في صفحة "جميع الصفحات" وفي البحث داخل الموقع، ما لم تكن هناك حالة خاصة
                        تستدعي الإخفاء بناءً على تواصل صاحب الصفحة مع الإدارة.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">هل يمكن لشخص آخر حذف الصفحة أو تعديلها؟</h2>
                    <p class="text-muted mb-0">
                        لا، لا يمكن لأي شخص تعديل الصفحة أو حذفها إلا من خلال رابط التعديل الخاص الذي حصلت عليه عند الإنشاء.
                        لذلك ننصح بعدم نشر رابط التعديل والاكتفاء بمشاركة رابط الصفحة التذكارية العامة.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">ما هي بطاقة الدعاء؟ وهل هي إجبارية؟</h2>
                    <p class="text-muted mb-0">
                        بطاقة الدعاء هي صورة جاهزة بتصميم جميل تحتوي على اسم المتوفى ودعاء مناسب، يمكن تحميلها ومشاركتها
                        على السوشيال ميديا. اختيار إنشائها اختياري عند إنشاء الصفحة، وليست إجبارية.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">هل الصور تخضع لمراجعة؟</h2>
                    <p class="text-muted mb-0">
                        نعم، يتم مراجعة الصور يدويًا للتأكد من ملاءمتها واحترامها لأحكام الشريعة والذوق العام. عادة تتم
                        الموافقة خلال 24 ساعة بإذن الله.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm bg-light mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-2">لم تجد سؤالك هنا؟</h2>
                    <p class="mb-3 text-muted">
                        إذا كان لديك سؤال آخر أو استفسار خاص، يمكنك التواصل معنا مباشرة.
                    </p>
                    <a href="<?= site_url('contact') ?>" class="btn btn-primary">اذهب إلى صفحة تواصل معنا</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

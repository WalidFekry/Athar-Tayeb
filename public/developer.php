<?php
/**
 * Developer Page
 * About the site developer and project information
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'عن المطور — ' . SITE_NAME;
$pageDescription = 'قصة المطوّر والقائم على هذا العمل الخيري، ورحلته في إنشاء موقع أثر طيب، بهدف نشر الصدقات الجارية الإلكترونية وإحياء ذكرى الأحبة بوسائل رقمية نافعة لوجه الله تعالى.';
$pageImage = BASE_URL . '/assets/images/profile-picture.png';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">

            <!-- Page Header -->
            <div class="text-center mb-5">
                <div class="developer-page-icon mb-3">💻</div>
                <h1 class="fw-bold">عن المطور</h1>
                <p class="lead text-muted">
                    قصة المشروع والقائم عليه
                </p>
            </div>

            <!-- Developer Profile Card -->
            <div class="card shadow-sm mb-4 developer-profile-card">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="developer-photo-placeholder">
                                <img src="<?= BASE_URL ?>/assets/images/profile-picture.png" alt="وليد فكري"
                                    class="developer-photo"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="photo-placeholder-icon" style="display: none;">
                                    👨‍💻
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h2 class="fw-bold text-primary mb-3">وليد فكري</h2>
                            <p class="lead mb-3">
                                مطور برمجيات ومبرمج متخصص في تطوير تطبيقات الويب والموبايل
                            </p>
                            <div class="developer-skills">
                                <span class="skill-badge">PHP</span>
                                <span class="skill-badge">JavaScript</span>
                                <span class="skill-badge">MySQL</span>
                                <span class="skill-badge">Bootstrap</span>
                                <span class="skill-badge">Android</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About the Project -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-primary mb-4">
                        <span class="section-icon">🌟</span>
                        عن المشروع
                    </h3>
                    <p class="mb-3">
                        هذا العمل خيري بحت وغير ربحي، وقد تَحَمَّل المطوّر كامل التكاليف والمصروفات البرمجية لإطلاق هذا
                        الموقع ابتغاء وجه الله تعالى.
                    </p>
                    <p class="mb-0">
                        جميع صفحات هذا الموقع غير قابلة للبيع أو للاستخدام التجاري تحت أي مسمّى.
                    </p>

                </div>
            </div>

            <!-- Project Features -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🌟</div>
                        <h5 class="fw-bold">عمل خيري</h5>
                        <p>مشروع مجاني بالكامل لوجه الله تعالى، بدون أي أهداف ربحية</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">💚</div>
                        <h5 class="fw-bold">صدقة جارية</h5>
                        <p>كل صفحة تذكارية هي صدقة جارية إلكترونية تبقى بعد الرحيل</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h5 class="fw-bold">غير تجاري</h5>
                        <p>الموقع غير قابل للبيع أو الاستخدام التجاري بأي شكل</p>
                    </div>
                </div>
            </div>

            <!-- Developer Mission -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-primary mb-4">
                        <span class="section-icon">🎯</span>
                        الرؤية والرسالة
                    </h3>
                    <div class="mission-content">
                        <div class="mission-item">
                            <h5 class="fw-bold">💡 الهدف</h5>
                            <p>توفير منصة رقمية سهلة ومجانية لإنشاء صفحات تذكارية للمتوفين، تساعد الأهل والأصدقاء على
                                الدعاء والذكر والقرآن لمن فارقونا.</p>
                        </div>
                        <div class="mission-item">
                            <h5 class="fw-bold">🚀 الطموح</h5>
                            <p>أن يكون هذا المشروع صدقة جارية تنفع الأحياء والأموات، وأن يستمر نفعه بإذن الله تعالى.</p>
                        </div>
                        <div class="mission-item">
                            <h5 class="fw-bold">🤝 المساهمة</h5>
                            <p>المشروع مفتوح المصدر على GitHub، ونرحب بأي مساهمات أو اقتراحات لتطوير المنصة.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prayer Section -->
            <div class="card shadow-sm prayer-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="prayer-icon mb-3">🤲</div>
                        <h3 class="fw-bold text-primary">نرجوا الدعاء للقائم على هذا العمل</h3>
                        <p class="text-muted">لتكون صدقة في محياه وبعد مماته</p>
                    </div>

                    <div class="prayer-text">
                        <div class="prayer-item">
                            <p>اللهم اجعل هذا العمل الذي قام به عبدك وليد فكري في ميزان حسناته، وبارك له في عمله ووقته،
                                واجعل كل سطر وحرف من هذا الموقع شهادة له بالخير وحجة له يوم القيامة.</p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم اجعل عمله خالصًا لوجهك الكريم، وارزقه الإبداع والتميز في كل عمله، ووفّقه لما تحب
                                وترضى.</p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم بارك له في علمه، وزد في فكره، وارزقه الإلهام والتوفيق والنجاح في كل ما يسعى إليه.
                            </p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم ارزقه رزقًا حلالًا طيبًا مباركًا فيه، وافتح له أبواب الخير والرزق، واكفه بحلالك عن
                                حرامك واغنه بفضلك عمن سواك.</p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم اجعله وأهله وأحباءه وأصدقاءه من أهل الجنة، واغفر لهم ما تقدم من ذنبهم وما تأخر،
                                وزدهم من فضلك ونعيمك.</p>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">
                            <strong>آمين يا رب العالمين</strong>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Developer Page Styles */
    .developer-page-icon {
        font-size: 4rem;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }

    .developer-profile-card {
        border: 2px solid var(--primary);
        background: linear-gradient(135deg, var(--muted-bg) 0%, var(--card-bg) 100%);
    }

    .developer-photo-placeholder {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid var(--primary);
        box-shadow: var(--shadow);
    }

    .developer-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-placeholder-icon {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5rem;
        background: linear-gradient(135deg, var(--primary) 0%, #6a9d5f 100%);
        color: white;
    }

    .developer-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .skill-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background-color: var(--primary);
        color: white;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .section-icon {
        font-size: 1.5rem;
        margin-left: 0.5rem;
    }

    .feature-card {
        background-color: var(--card-bg);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 2rem;
        text-align: center;
        height: 100%;
        transition: var(--transition);
    }

    .feature-card:hover {
        border-color: var(--primary);
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .feature-card h5 {
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .feature-card p {
        color: var(--muted-text);
        line-height: 1.6;
        margin: 0;
    }

    .mission-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .mission-item h5 {
        color: var(--primary);
        margin-bottom: 0.75rem;
    }

    .mission-item p {
        color: var(--text);
        line-height: 1.8;
        margin: 0;
    }

    .prayer-card {
        background: linear-gradient(135deg, rgba(90, 125, 78, 0.05) 0%, var(--card-bg) 100%);
        border: 2px solid var(--primary);
    }

    .prayer-icon {
        font-size: 3rem;
    }

    .prayer-text {
        max-width: 800px;
        margin: 0 auto;
    }

    .prayer-item {
        padding: 1.25rem;
        margin-bottom: 1rem;
        background-color: rgba(255, 255, 255, 0.5);
        border-right: 4px solid var(--primary);
        border-radius: 8px;
        transition: var(--transition);
    }

    [data-theme="dark"] .prayer-item {
        background-color: rgba(0, 0, 0, 0.2);
    }

    .prayer-item:hover {
        background-color: rgba(90, 125, 78, 0.1);
        transform: translateX(-5px);
    }

    .prayer-item p {
        color: var(--text);
        font-size: 1.05rem;
        line-height: 2;
        margin: 0;
        font-family: var(--font-ar);
    }

    @media (max-width: 768px) {
        .developer-page-icon {
            font-size: 3rem;
        }

        .developer-photo-placeholder {
            width: 150px;
            height: 150px;
        }

        .photo-placeholder-icon {
            font-size: 4rem;
        }

        .skill-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }

        .feature-card {
            padding: 1.5rem;
        }

        .feature-icon {
            font-size: 2.5rem;
        }

        .prayer-item {
            padding: 1rem;
        }

        .prayer-item p {
            font-size: 0.95rem;
            line-height: 1.8;
        }
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
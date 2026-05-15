<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminPasswordApprovalController;
use App\Http\Controllers\Admin\AdminPasswordChangeController;
use App\Http\Controllers\Admin\ConsultationCustomizationController;
use App\Http\Controllers\Admin\DocumentationManagementController;
use App\Http\Controllers\Admin\FooterCustomizationController;
use App\Http\Controllers\Admin\HomeCustomizationController;
use App\Http\Controllers\Admin\ManagementCustomizationController;
use App\Http\Controllers\Admin\NavbarCustomizationController;
use App\Http\Controllers\Admin\ProfileCustomizationController;
use App\Http\Controllers\Admin\ProgramWorkCustomizationController;
use App\Http\Controllers\Admin\SecurityApproverApprovalController;
use App\Http\Controllers\Admin\SecurityApproverController;
use App\Http\Controllers\Admin\ServiceCustomizationController;
use App\Http\Controllers\User\DokumentasiController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\KepengurusanController;
use App\Http\Controllers\User\KonsultasiController;
use App\Http\Controllers\User\LayananJasaController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ProgramKerjaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('beranda');

Route::get('/profil', [ProfileController::class, 'index'])
    ->name('profil');

Route::get('/kepengurusan', [KepengurusanController::class, 'index'])
    ->name('kepengurusan');

Route::get('/program-kerja', [ProgramKerjaController::class, 'index'])
    ->name('program-kerja');

Route::get('/layananJasa', [LayananJasaController::class, 'index'])
    ->name('layananJasa');

Route::post('/layananJasa/testimoni', [LayananJasaController::class, 'storeTestimonial'])
    ->middleware('throttle:8,1')
    ->name('layananJasa.testimoni.store');

Route::get('/konsultasi', [KonsultasiController::class, 'index'])
    ->name('konsultasi');

Route::post('/konsultasi/testimoni', [KonsultasiController::class, 'storeTestimonial'])
    ->middleware('throttle:8,1')
    ->name('konsultasi.testimoni.store');

Route::get('/kontak', fn() => Inertia::render('user/pages/Kontak'))
    ->name('kontak');

Route::get('/dokumentasi', [DokumentasiController::class, 'index'])
    ->name('dokumentasi');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Admin Authentication
        |--------------------------------------------------------------------------
        */

        Route::get('/login', [AdminAuthController::class, 'create'])
            ->name('login');

        Route::post('/login', [AdminAuthController::class, 'store'])
            ->name('login.store');

        /*
        |--------------------------------------------------------------------------
        | Password Approval Routes
        |--------------------------------------------------------------------------
        | Route ini sengaja berada di luar middleware admin.auth karena Ketua HMPS
        | tidak perlu login sebagai admin untuk menyetujui atau menolak request
        | ganti password.
        */

        Route::get('/password-approval/{token}', [AdminPasswordApprovalController::class, 'show'])
            ->middleware('signed')
            ->name('password.approval.show');

        Route::post('/password-approval/{token}/approve', [AdminPasswordApprovalController::class, 'approve'])
            ->name('password.approval.approve');

        Route::post('/password-approval/{token}/reject', [AdminPasswordApprovalController::class, 'reject'])
            ->name('password.approval.reject');

        /*
        |--------------------------------------------------------------------------
        | Security Approver Approval Routes
        |--------------------------------------------------------------------------
        | Route ini juga di luar middleware admin.auth karena Ketua HMPS aktif
        | harus bisa membuka link approval dari WhatsApp tanpa login admin.
        */

        Route::get('/security/approver-approval/{token}', [SecurityApproverApprovalController::class, 'show'])
            ->middleware('signed')
            ->name('security.approver-approval.show');

        Route::post('/security/approver-approval/{token}/approve', [SecurityApproverApprovalController::class, 'approve'])
            ->name('security.approver-approval.approve');

        Route::post('/security/approver-approval/{token}/reject', [SecurityApproverApprovalController::class, 'reject'])
            ->name('security.approver-approval.reject');

        /*
        |--------------------------------------------------------------------------
        | Protected Admin Routes
        |--------------------------------------------------------------------------
        | Semua route di dalam group ini wajib login sebagai admin.
        */

        Route::middleware('admin.auth')->group(function () {
            Route::redirect('/', '/admin/dashboard');

            Route::get('/dashboard', fn() => Inertia::render('admin/pages/Dashboard'))
                ->name('dashboard');

            Route::post('/logout', [AdminAuthController::class, 'destroy'])
                ->name('logout');

            /*
            |--------------------------------------------------------------------------
            | Admin Change Password
            |--------------------------------------------------------------------------
            */

            Route::prefix('password')
                ->name('password.')
                ->controller(AdminPasswordChangeController::class)
                ->group(function () {
                    Route::get('/change', 'index')
                        ->name('change');

                    Route::post('/request', 'storeRequest')
                        ->name('request');

                    Route::put('/{passwordChangeRequest}', 'updatePassword')
                        ->name('update');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Security Approver
            |--------------------------------------------------------------------------
            */

            Route::prefix('security')
                ->name('security.')
                ->controller(SecurityApproverController::class)
                ->group(function () {
                    Route::get('/approver', 'index')
                        ->name('approver.index');

                    Route::post('/approver/request-change', 'storeChangeRequest')
                        ->name('approver.request-change');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Navbar
            |--------------------------------------------------------------------------
            */

            Route::prefix('navbar')
                ->name('navbar.')
                ->controller(NavbarCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::post('/settings', 'updateSetting')
                        ->name('settings.update');

                    Route::post('/items', 'storeItem')
                        ->name('items.store');

                    Route::put('/items/{navigationItem}', 'updateItem')
                        ->name('items.update');

                    Route::delete('/items/{navigationItem}', 'destroyItem')
                        ->name('items.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Footer
            |--------------------------------------------------------------------------
            */

            Route::prefix('footer')
                ->name('footer.')
                ->controller(FooterCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::post('/settings', 'updateSetting')
                        ->name('settings.update');

                    Route::post('/nav-items', 'storeNavItem')
                        ->name('nav-items.store');

                    Route::put('/nav-items/{footerNavItem}', 'updateNavItem')
                        ->name('nav-items.update');

                    Route::delete('/nav-items/{footerNavItem}', 'destroyNavItem')
                        ->name('nav-items.destroy');

                    Route::post('/social-links', 'storeSocialLink')
                        ->name('social-links.store');

                    Route::put('/social-links/{footerSocialLink}', 'updateSocialLink')
                        ->name('social-links.update');

                    Route::delete('/social-links/{footerSocialLink}', 'destroySocialLink')
                        ->name('social-links.destroy');

                    Route::post('/contact-items', 'storeContactItem')
                        ->name('contact-items.store');

                    Route::put('/contact-items/{footerContactItem}', 'updateContactItem')
                        ->name('contact-items.update');

                    Route::delete('/contact-items/{footerContactItem}', 'destroyContactItem')
                        ->name('contact-items.destroy');

                    Route::post('/legal-links', 'storeLegalLink')
                        ->name('legal-links.store');

                    Route::put('/legal-links/{footerLegalLink}', 'updateLegalLink')
                        ->name('legal-links.update');

                    Route::delete('/legal-links/{footerLegalLink}', 'destroyLegalLink')
                        ->name('legal-links.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Home
            |--------------------------------------------------------------------------
            */

            Route::prefix('home')
                ->name('home.')
                ->controller(HomeCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::put('/sections/{homeSection}', 'updateSection')
                        ->name('sections.update');

                    Route::post('/tickers', 'storeTicker')
                        ->name('tickers.store');

                    Route::put('/tickers/{homeTicker}', 'updateTicker')
                        ->name('tickers.update');

                    Route::delete('/tickers/{homeTicker}', 'destroyTicker')
                        ->name('tickers.destroy');

                    Route::post('/highlights', 'storeHighlight')
                        ->name('highlights.store');

                    Route::put('/highlights/{homeHighlight}', 'updateHighlight')
                        ->name('highlights.update');

                    Route::delete('/highlights/{homeHighlight}', 'destroyHighlight')
                        ->name('highlights.destroy');

                    Route::post('/media', 'storeMedia')
                        ->name('media.store');

                    Route::put('/media/{homeMedia}', 'updateMedia')
                        ->name('media.update');

                    Route::delete('/media/{homeMedia}', 'destroyMedia')
                        ->name('media.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Dokumentasi
            |--------------------------------------------------------------------------
            */

            Route::prefix('dokumentasi')
                ->name('dokumentasi.')
                ->controller(DocumentationManagementController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::post('/', 'store')
                        ->name('store');

                    Route::post('/{documentation}', 'update')
                        ->name('update');

                    Route::delete('/{documentation}', 'destroy')
                        ->name('destroy');

                    Route::delete('/images/{documentationImage}', 'destroyImage')
                        ->name('images.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Profil
            |--------------------------------------------------------------------------
            */

            Route::prefix('profil')
                ->name('profil.')
                ->controller(ProfileCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::put('/sections/{profileSection}', 'updateSection')
                        ->name('sections.update');

                    Route::post('/items', 'storeItem')
                        ->name('items.store');

                    Route::put('/items/{profileItem}', 'updateItem')
                        ->name('items.update');

                    Route::delete('/items/{profileItem}', 'destroyItem')
                        ->name('items.destroy');

                    Route::post('/hero-images', 'storeHeroImage')
                        ->name('hero-images.store');

                    Route::post('/hero-images/{profileHeroImage}', 'updateHeroImage')
                        ->name('hero-images.update');

                    Route::delete('/hero-images/{profileHeroImage}', 'destroyHeroImage')
                        ->name('hero-images.destroy');

                    /*
                    |--------------------------------------------------------------------------
                    | Admin Custom Profil - Cabinet Logo
                    |--------------------------------------------------------------------------
                    | URL final:
                    | POST   /admin/profil/cabinet-logo
                    | POST   /admin/profil/cabinet-logo/{profileCabinetLogo}
                    | DELETE /admin/profil/cabinet-logo/{profileCabinetLogo}
                    */

                    Route::post('/cabinet-logo', 'storeCabinetLogoImage')
                        ->name('cabinet-logo.store');

                    Route::post('/cabinet-logo/{profileCabinetLogo}', 'updateCabinetLogoImage')
                        ->name('cabinet-logo.update');

                    Route::delete('/cabinet-logo/{profileCabinetLogo}', 'destroyCabinetLogoImage')
                        ->name('cabinet-logo.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Kepengurusan
            |--------------------------------------------------------------------------
            */

            Route::prefix('kepengurusan')
                ->name('kepengurusan.')
                ->controller(ManagementCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::post('/hero/{managementHeroSetting}', 'updateHeroSetting')
                        ->name('hero.update');

                    Route::post('/divisions', 'storeDivision')
                        ->name('divisions.store');

                    Route::put('/divisions/{managementDivision}', 'updateDivision')
                        ->name('divisions.update');

                    Route::delete('/divisions/{managementDivision}', 'destroyDivision')
                        ->name('divisions.destroy');

                    Route::post('/members', 'storeMember')
                        ->name('members.store');

                    Route::put('/members/{managementMember}', 'updateMember')
                        ->name('members.update');

                    Route::delete('/members/{managementMember}', 'destroyMember')
                        ->name('members.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Program Kerja
            |--------------------------------------------------------------------------
            */

            Route::prefix('program-kerja')
                ->name('program-kerja.')
                ->controller(ProgramWorkCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::post('/categories', 'storeCategory')
                        ->name('categories.store');

                    Route::put('/categories/{programWorkCategory}', 'updateCategory')
                        ->name('categories.update');

                    Route::delete('/categories/{programWorkCategory}', 'destroyCategory')
                        ->name('categories.destroy');

                    Route::post('/programs', 'storeProgram')
                        ->name('programs.store');

                    Route::put('/programs/{programWork}', 'updateProgram')
                        ->name('programs.update');

                    Route::delete('/programs/{programWork}', 'destroyProgram')
                        ->name('programs.destroy');
                    Route::post('/hero/{programWorkHeroSetting}', 'updateHeroSetting')
                        ->name('hero.update');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Layanan Jasa
            |--------------------------------------------------------------------------
            */

            Route::prefix('layanan-jasa')
                ->name('layanan-jasa.')
                ->controller(ServiceCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::post('/services', 'storeService')
                        ->name('services.store');

                    Route::put('/services/{serviceCatalog}', 'updateService')
                        ->name('services.update');

                    Route::delete('/services/{serviceCatalog}', 'destroyService')
                        ->name('services.destroy');

                    Route::post('/packages', 'storePackage')
                        ->name('packages.store');

                    Route::put('/packages/{servicePackage}', 'updatePackage')
                        ->name('packages.update');

                    Route::delete('/packages/{servicePackage}', 'destroyPackage')
                        ->name('packages.destroy');

                    Route::put('/testimonials/{clientTestimonial}', 'updateTestimonial')
                        ->name('testimonials.update');

                    Route::delete('/testimonials/{clientTestimonial}', 'destroyTestimonial')
                        ->name('testimonials.destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Admin Custom Konsultasi
            |--------------------------------------------------------------------------
            */

            Route::prefix('konsultasi')
                ->name('konsultasi.')
                ->controller(ConsultationCustomizationController::class)
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index');

                    Route::put('/sections/{consultationSection}', 'updateSection')
                        ->name('sections.update');

                    Route::post('/admins', 'storeAdmin')
                        ->name('admins.store');

                    Route::put('/admins/{consultationAdmin}', 'updateAdmin')
                        ->name('admins.update');

                    Route::delete('/admins/{consultationAdmin}', 'destroyAdmin')
                        ->name('admins.destroy');

                    Route::post('/steps', 'storeStep')
                        ->name('steps.store');

                    Route::put('/steps/{consultationStep}', 'updateStep')
                        ->name('steps.update');

                    Route::delete('/steps/{consultationStep}', 'destroyStep')
                        ->name('steps.destroy');

                    Route::post('/tickers', 'storeTicker')
                        ->name('tickers.store');

                    Route::put('/tickers/{consultationTicker}', 'updateTicker')
                        ->name('tickers.update');

                    Route::delete('/tickers/{consultationTicker}', 'destroyTicker')
                        ->name('tickers.destroy');

                    /*
                    |--------------------------------------------------------------------------
                    | Admin Custom Konsultasi - Testimonials
                    |--------------------------------------------------------------------------
                    | URL final:
                    | POST   /admin/konsultasi/testimonials
                    | PUT    /admin/konsultasi/testimonials/{clientTestimonial}
                    | PATCH  /admin/konsultasi/testimonials/{clientTestimonial}/approve
                    | PATCH  /admin/konsultasi/testimonials/{clientTestimonial}/unapprove
                    | DELETE /admin/konsultasi/testimonials/{clientTestimonial}
                    */

                    Route::patch('/testimonials/{clientTestimonial}/approve', 'approveTestimonial')
                        ->name('testimonials.approve');

                    Route::patch('/testimonials/{clientTestimonial}/unapprove', 'unapproveTestimonial')
                        ->name('testimonials.unapprove');

                    Route::delete('/testimonials/{clientTestimonial}', 'destroyTestimonial')
                        ->name('testimonials.destroy');
                });
        });
    });

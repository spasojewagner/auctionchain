<?php $__env->startSection('title', 'Nova aukcija'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-card" data-aos="fade-up">
                <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Postavi novu aukciju</h2>

                <form method="POST" action="<?php echo e(route('auctions.store')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Naziv predmeta</label>
                        <input type="text" name="title" id="auction-title"
                               class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               value="<?php echo e(old('title')); ?>" required>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Opis</label>
                            <button type="button" id="ai-describe-btn" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-wand-magic-sparkles me-1"></i>Generiši opis (AI)
                            </button>
                        </div>
                        <textarea name="description" id="auction-description" rows="5"
                                  class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategorija</label>
                            <select name="category_id" id="auction-category" class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="">-- Izaberi --</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cat->id); ?>" data-name="<?php echo e($cat->name); ?>" <?php echo e(old('category_id') == $cat->id ? 'selected' : ''); ?>>
                                        <?php echo e($cat->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Početna cena (RSD)</label>
                            <input type="number" name="starting_price" min="1" step="0.01"
                                   class="form-control <?php $__errorArgs = ['starting_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('starting_price')); ?>" required>
                            <?php $__errorArgs = ['starting_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Trajanje (sati)</label>
                            <select name="duration_hours" class="form-select" required>
                                <option value="6">6 sati</option>
                                <option value="24" selected>1 dan</option>
                                <option value="72">3 dana</option>
                                <option value="168">7 dana</option>
                                <option value="336">14 dana</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">"Kupi odmah" cena (RSD) — opciono</label>
                        <input type="number" name="buy_now_price" min="1" step="0.01"
                               class="form-control <?php $__errorArgs = ['buy_now_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               value="<?php echo e(old('buy_now_price')); ?>" placeholder="Ostavi prazno ako ne želiš opciju">
                        <small class="text-muted">Mora biti veća od početne cene. Kupac može momentalno kupiti predmet po ovoj ceni.</small>
                        <?php $__errorArgs = ['buy_now_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slike (1-5, max 5MB svaka)</label>
                        <input type="file" name="images[]" id="auction-images" multiple accept="image/*"
                               class="form-control <?php $__errorArgs = ['images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <small class="text-muted">Prva slika će biti glavna (thumbnail).</small>
                        <?php $__errorArgs = ['images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php $__errorArgs = ['images.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom btn-ripple">
                            <i class="fas fa-check me-2"></i>Postavi aukciju
                        </button>
                        <a href="<?php echo e(route('auctions.index')); ?>" class="btn btn-outline-secondary">Otkaži</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.getElementById('ai-describe-btn')?.addEventListener('click', async function () {
    const btn = this;
    const title = document.getElementById('auction-title').value.trim();
    const catSelect = document.getElementById('auction-category');
    const category = catSelect.options[catSelect.selectedIndex]?.dataset.name || '';
    const descEl = document.getElementById('auction-description');
    const fileInput = document.getElementById('auction-images');
    const hasImage = fileInput.files && fileInput.files.length > 0;

    if (!title && !hasImage) {
        showToast('danger', 'Unesite naziv ili izaberite sliku.');
        return;
    }

    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + (hasImage ? 'Analiziram sliku...' : 'Generišem...');

    try {
        let res;
        if (hasImage) {
            const fd = new FormData();
            fd.append('image', fileInput.files[0]);
            fd.append('title', title);
            fd.append('category', category);
            res = await fetch('<?php echo e(route("ai.describe-image")); ?>', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: fd
            });
        } else {
            res = await fetch('<?php echo e(route("ai.describe")); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ title, category })
            });
        }

        const data = await res.json();
        if (data.success) {
            descEl.value = data.description;
            showToast('success', 'Opis generisan!');
        } else {
            showToast('danger', data.message || 'AI nije uspeo da generiše opis.');
        }
    } catch (e) {
        showToast('danger', 'Greška pri pozivu AI servisa.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = original;
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Metoda\Desktop\auctionchain\resources\views/auctions/create.blade.php ENDPATH**/ ?>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraScripts)): ?>
    <?= $extraScripts ?>
<?php endif; ?>
<script>
    window.CSPI10 = {
        csrfToken: <?= json_encode(\App\Core\Security::csrfToken(), JSON_THROW_ON_ERROR) ?>,
        baseUrl: <?= json_encode(url(''), JSON_THROW_ON_ERROR) ?>
    };
</script>
</body>
</html>

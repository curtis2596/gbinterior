<header class="text-center mb-4">
        <?php

        use App\Helpers\FileUtility;

            if (FileUtility::isExist($company->logo_for_pdf)):
        ?>
            <img style="max-width: 200px; max-height: 100px;" src="<?= FileUtility::get($company->logo_for_pdf) ?>" />
        <?php endif; ?>
        <h2>
            {{ $company->name }}
        </h2> 
        <p class="mx-auto w-50" style="line-height: 1.6;">{{ $company->address }}</p>
        <strong>GST No. {{ $company->gst_number }}</strong>

    </header>
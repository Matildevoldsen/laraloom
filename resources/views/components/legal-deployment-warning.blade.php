@props(['missingLegalDetails'])

@if ($missingLegalDetails !== [])
    <section role="alert" class="rounded-2xl bg-amber-50 px-5 py-4 text-sm text-amber-950 dark:bg-amber-300/10 dark:text-amber-100">
        <h2 class="font-semibold">Deployment information required</h2>
        <p class="mt-1 leading-6">
            This document deliberately does not invent the operator's identity or retention periods. Do not describe it as launch-ready until these values are configured:
        </p>
        <ul class="mt-3 grid list-disc gap-x-8 gap-y-1 pl-5 sm:grid-cols-2">
            @foreach ($missingLegalDetails as $missingLegalDetail)
                <li>{{ str($missingLegalDetail)->ucfirst() }}</li>
            @endforeach
        </ul>
    </section>
@endif

@extends('layouts.community', ['title' => 'Terms'])

@section('content')
    <article class="loom-card p-6 sm:p-9"><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Plain-language terms</p><h1 class="mt-3 text-3xl font-semibold tracking-[-.04em]">Community terms</h1>
        <div class="mt-9 space-y-8 text-sm leading-7 text-zinc-400">
            <section><h2 class="text-base font-semibold text-zinc-200">Your account</h2><p class="mt-2">Keep your credentials secure and provide accurate information. You are responsible for activity under your account.</p></section>
            <section><h2 class="text-base font-semibold text-zinc-200">Your content stays yours</h2><p class="mt-2">You retain ownership of content you submit. You grant Laraloom a non-exclusive, worldwide, royalty-free licence to host, display, format, and distribute that content solely to operate and promote the service. You can remove your posts and projects.</p></section>
            <section><h2 class="text-base font-semibold text-zinc-200">Only publish what you may share</h2><p class="mt-2">You confirm you have the rights needed to submit your content and that it does not infringe intellectual property, privacy, or other rights. Links and brief commentary should credit the original creator.</p></section>
            <section><h2 class="text-base font-semibold text-zinc-200">Moderation and availability</h2><p class="mt-2">We may remove content, restrict accounts, or change the service to protect the community and comply with law. The service is provided as available, without a guarantee of uninterrupted operation.</p></section>
            <section><h2 class="text-base font-semibold text-zinc-200">Questions and notices</h2><p class="mt-2">Send rights notices and content concerns through the <a class="text-[#ff7693]" href="{{ route('legal.content-request') }}">content request form</a>. Include enough detail for us to identify and assess the material.</p></section>
        </div>
        <p class="mt-9 border-t border-white/8 pt-5 text-xs text-zinc-600">Last updated {{ now()->format('j F Y') }}. Before a public commercial launch, these terms should be reviewed for the operator's jurisdiction and legal identity.</p>
    </article>
@endsection

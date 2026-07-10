@extends('layouts.community', ['title' => 'Privacy'])

@section('content')
    <article class="loom-card p-6 sm:p-9"><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Privacy</p><h1 class="mt-3 text-3xl font-semibold tracking-[-.04em]">What Laraloom keeps</h1>
        <div class="mt-9 space-y-8 text-sm leading-7 text-zinc-400">
            <section><h2 class="text-base font-semibold text-zinc-200">Community accounts</h2><p class="mt-2">We store the account and profile details you provide, your posts and projects, follows, reactions, bookmarks, security credentials, and basic service logs needed to operate and protect Laraloom.</p></section>
            <section><h2 class="text-base font-semibold text-zinc-200">Content requests</h2><p class="mt-2">Rights and correction requests include your name, email, relationship to the work, affected URL, and explanation. We use this information to assess, document, and respond to the request.</p></section>
            <section><h2 class="text-base font-semibold text-zinc-200">Automated discovery</h2><p class="mt-2">Tavily receives a search query and approved source domains. Azure OpenAI receives returned source metadata and short snippets. Laraloom configures raw-content retention off and does not send member account data through the curation agent.</p></section>
            <section><h2 class="text-base font-semibold text-zinc-200">Your choices</h2><p class="mt-2">Members can edit their profile and remove their own posts and projects. Publishers can request correction, removal, or source exclusion through the <a class="text-[#ff7693]" href="{{ route('legal.content-request') }}">content request form</a>.</p></section>
        </div>
        <p class="mt-9 border-t border-white/8 pt-5 text-xs text-zinc-600">Last updated {{ now()->format('j F Y') }}. The production operator should add its legal identity, jurisdiction-specific rights process, retention periods, and subprocessors before public launch.</p>
    </article>
@endsection

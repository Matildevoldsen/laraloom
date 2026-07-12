@extends('layouts.community', ['title' => 'Terms of Service'])

@section('content')
    <article class="loom-card overflow-hidden">
        <header class="px-6 py-8 sm:px-9 sm:py-10">
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-rose-600 dark:text-[#ff7693]">Plain-language terms</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.04em] text-zinc-950 dark:text-white">Terms of Service</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                These Terms govern your use of {{ config('app.name') }}. They explain the community rules, what happens to content you share, and the limits of private messaging and moderation.
            </p>
            <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-500">Effective {{ $legal['effective_date'] }} · Version {{ $legal['terms_version'] }}</p>
        </header>

        <div class="grid gap-10 px-6 pb-9 sm:px-9">
            <x-legal-deployment-warning :$missingLegalDetails />

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">1. Operator and agreement</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    These Terms are an agreement between you and
                    <strong class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $legal['operator']['name'] ?: 'the legal operator named in the deployment notice above' }}</strong>.
                    You agree to these Terms by selecting “I have read and agree” and continuing. Laraloom records the Terms version, acceptance time, and the minimum-age threshold presented. Materially revised Terms must be reviewed and accepted before member features can be used again. A privacy notice explains data use and is not a request for blanket consent; see our
                    <a class="font-medium text-rose-600 hover:underline dark:text-[#ff7693]" href="{{ route('legal.privacy') }}">Privacy Policy</a>.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">2. Eligibility and accounts</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    You must be at least {{ $legal['minimum_age'] }} years old and legally capable of entering this agreement. Laraloom does not permit accounts for anyone under {{ $legal['minimum_age'] }}, including with parent or guardian permission. The current age control is a self-declaration, not verified age assurance, and does not collect your date of birth.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    GitHub is the primary sign-in provider. You must keep your GitHub account secure and provide accurate account information. We do not receive your GitHub password. You are responsible for activity under your account until you tell us it has been compromised.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">3. Public community features</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Profiles, names, usernames, biographies, profile photos, follower and following relationships, public posts, projects, replies, reactions, and reposts are designed to be visible to other people and may be indexed or shared outside {{ config('app.name') }}. Bookmarks and direct messages are not public features.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">4. Your content and our limited licence</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    You keep ownership of content you submit. You give the operator a non-exclusive, worldwide, royalty-free licence to host, store, reproduce, format, and display it only as needed to operate, secure, and present the service, including ordinary previews and links to public posts. Processors acting for the operator may perform those limited tasks.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    The licence ends when content is deleted, except for temporary backups, copies already shared with or retained for another participant, and limited records reasonably required for safety, legal compliance, dispute resolution, or enforcement. You confirm that you have the rights and permissions needed to share your content.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">5. Direct messages</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    A member may start a direct-message conversation only with someone who follows that member. Participants may then reply in that conversation. Do not use messages for unsolicited marketing, harassment, threats, fraud, or attempts to evade a block or account restriction.
                </p>
                <div class="rounded-2xl bg-zinc-100/80 px-5 py-4 text-sm leading-7 text-zinc-700 dark:bg-white/[.045] dark:text-zinc-300">
                    <strong class="font-semibold text-zinc-950 dark:text-white">Encryption boundary:</strong>
                    message bodies are encrypted in transit and while stored, but they are not end-to-end encrypted. The service manages the encryption keys and can decrypt a message to deliver it to an authorized participant. Vask real-time events carry conversation, message, sender, and timestamp identifiers—not decrypted message bodies. An authorized participant fetches the body over HTTPS.
                </div>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Messages do not disappear and there is currently no per-message delete-for-everyone feature. While both accounts remain, history stays visible to both participants even if the follow relationship ends. Deleting either participant's account removes the live conversation history from the primary database; backups and limited safety or legal records follow the configured retention schedule. Administrators have no general ability to browse conversations. When a participant reports a conversation, the selected messages and related identifiers are provided to authorized safety staff for review.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">6. Acceptable use</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">You must not use the service to:</p>
                <ul class="grid list-disc gap-2 pl-5 text-sm leading-6 text-zinc-600 dark:text-zinc-400 sm:grid-cols-2 sm:gap-x-10">
                    <li>break the law or encourage illegal activity;</li>
                    <li>threaten, harass, stalk, exploit, or target people with hateful abuse;</li>
                    <li>impersonate others or misrepresent affiliation;</li>
                    <li>publish private information, credentials, or precise location without permission;</li>
                    <li>share child sexual abuse material or sexually exploit a minor;</li>
                    <li>share non-consensual intimate imagery, including manipulated or AI-generated depictions;</li>
                    <li>infringe intellectual-property, privacy, publicity, or other rights;</li>
                    <li>send spam, scams, malware, or deceptive links;</li>
                    <li>interfere with security, scrape restricted areas, or bypass access controls; or</li>
                    <li>evade moderation, blocks, rate limits, or account restrictions.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">7. Moderation, reports, and reasons</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Authorized administrators may review public content and submitted reports. Current automated systems support spam prevention, rate limiting, security, and source curation; they do not proactively read direct-message bodies or make final legal-content decisions. Depending on context, the operator may label, reduce distribution of, remove, or preserve content; warn or restrict an account; or suspend or terminate access.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Decisions should be timely, diligent, objective, and proportionate. Before serving EU recipients, the moderation workflow must give affected people the reasons, automation disclosure, duration and scope, and review or appeal options required by law. Significant changes to moderation rules must be notified before they take effect.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">8. Illegal content and intimate-image requests</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Anyone may report specific allegedly illegal content through the
                    <a class="font-medium text-rose-600 hover:underline dark:text-[#ff7693]" href="{{ route('legal.content-request') }}">content request form</a>.
                    Give the exact location or message identifiers, the reason it is unlawful, and accurate contact information. Reports are assessed under applicable law and these Terms. Before serving EU recipients, the workflow must provide the receipt, outcome notice, and redress information required by law.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    The same route accepts requests concerning real or digitally altered intimate images shared without consent. Do not re-upload the image with your report. Where the US TAKE IT DOWN Act applies, a valid request requires removal of the reported depiction and known identical copies within 48 hours. The operator must maintain the workflow needed to meet that deadline before public launch.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">9. Suspension and account closure</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    You may stop using the service and request account deletion. The operator may restrict or end access for serious or repeated violations, legal requirements, material security risk, or prolonged unavailability. Where permitted, notice and a chance to seek review are provided. Account closure does not erase obligations or records that lawfully need to survive.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">10. Third-party services</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    GitHub provides sign-in, Cloudflare R2 stores uploaded media, and Vask provides real-time delivery events. Those providers may apply their own terms to their services. Links and user content may lead to third-party sites that the operator does not control or endorse.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">11. Service changes and availability</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    The service may change as features and legal requirements develop. Material changes to these Terms will be presented clearly and require acceptance of a new version before member features can be used. The service is provided on an “as available” basis; no promise is made that it will always be uninterrupted, error-free, or suitable for every purpose.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">12. Responsibility and liability</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Users are responsible for their content and conduct. To the fullest extent applicable law permits, the operator is not responsible for user content or indirect losses that were not reasonably foreseeable. Nothing in these Terms excludes mandatory consumer rights or liability that cannot legally be excluded or limited, including liability for fraud or deliberate wrongdoing. No monetary liability cap is stated until the operator and governing jurisdiction are configured and reviewed.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">13. Governing rules and contact</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Mandatory protections and courts available to consumers where they live are not displaced by these Terms. The operator's governing-law and forum clause must be completed after its legal identity and country are configured.
                </p>
                <dl class="grid gap-3 rounded-2xl bg-zinc-100/80 p-5 text-sm dark:bg-white/[.045] sm:grid-cols-[10rem_1fr]">
                    <dt class="font-medium text-zinc-700 dark:text-zinc-300">Operator</dt>
                    <dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['operator']['name'] ?: 'Not configured' }}</dd>
                    <dt class="font-medium text-zinc-700 dark:text-zinc-300">Postal address</dt>
                    <dd class="whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $legal['operator']['postal_address'] ?: 'Not configured' }}</dd>
                    <dt class="font-medium text-zinc-700 dark:text-zinc-300">Legal contact</dt>
                    <dd class="text-zinc-600 dark:text-zinc-400">
                        @if (filled($legal['operator']['legal_email']))
                            <a class="text-rose-600 hover:underline dark:text-[#ff7693]" href="mailto:{{ $legal['operator']['legal_email'] }}">{{ $legal['operator']['legal_email'] }}</a>
                        @else
                            Not configured
                        @endif
                    </dd>
                </dl>
            </section>
        </div>
    </article>
@endsection

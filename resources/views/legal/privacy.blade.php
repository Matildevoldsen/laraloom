@extends('layouts.community', ['title' => 'Privacy Policy'])

@section('content')
    <article class="loom-card overflow-hidden">
        <header class="px-6 py-8 sm:px-9 sm:py-10">
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-rose-600 dark:text-[#ff7693]">Privacy</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-[-.04em] text-zinc-950 dark:text-white">Privacy Policy</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                This policy explains what {{ config('app.name') }} collects, why it is used, who can see it, and the controls available to you. It describes the current product; it does not claim that publishing a policy alone satisfies every law.
            </p>
            <p class="mt-3 text-xs text-zinc-500">Effective {{ $legal['effective_date'] }} · Version {{ $legal['privacy_version'] }}</p>
        </header>

        <div class="grid gap-10 px-6 pb-9 sm:px-9">
            <x-legal-deployment-warning :$missingLegalDetails />

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">1. Who is responsible</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    The data controller is <strong class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $legal['operator']['name'] ?: 'not yet configured' }}</strong>, at
                    <span class="whitespace-pre-line">{{ $legal['operator']['postal_address'] ?: 'an address not yet configured' }}</span>.
                    The operator decides why and how personal information is used. Contact details appear at the end of this policy.
                </p>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">2. Information we collect and its source</h2>
                <div class="overflow-x-auto rounded-2xl bg-zinc-100/80 dark:bg-white/[.045]">
                    <table class="min-w-full text-left text-sm leading-6">
                        <thead class="text-zinc-800 dark:text-zinc-200">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Category</th>
                                <th class="px-5 py-3 font-semibold">What it includes and where it comes from</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200/70 text-zinc-600 dark:divide-white/6 dark:text-zinc-400">
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">GitHub sign-in</th>
                                <td class="px-5 py-4">The base identity response and, only when needed, the <code class="text-xs">user:email</code> scope provide an immutable GitHub ID, login, display name, primary verified email, and GitHub avatar URL. We do not request repository or organisation scopes and do not store OAuth access or refresh tokens.</td>
                            </tr>
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">Profile and relationships</th>
                                <td class="px-5 py-4">Your name, username and time of the last username change, biography, profile photo, links, location, selected stack, work availability, follower and following relationships, and profile settings. Most comes from you; the initial name and avatar may come from GitHub.</td>
                            </tr>
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">Eligibility and agreement records</th>
                                <td class="px-5 py-4">The Terms version accepted, acceptance time, Privacy Policy version presented with that flow, and the minimum-age threshold you confirmed. We record that you confirmed you are at least {{ $legal['minimum_age'] }}; we do not ask for or store your date of birth.</td>
                            </tr>
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">Community activity</th>
                                <td class="px-5 py-4">Posts, projects, media, replies, reactions, reposts, bookmarks, tags, links, publication times, and related records. Public contributions may incidentally reveal sensitive traits; we do not use those traits for advertising or profiling.</td>
                            </tr>
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">Direct messages</th>
                                <td class="px-5 py-4">Encrypted message bodies, participant IDs, conversation and message IDs, sender ID, timestamps, and delivery state. Participants supply message content; the service creates delivery metadata.</td>
                            </tr>
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">Media storage</th>
                                <td class="px-5 py-4">User-uploaded profile photos and post attachments are private objects in Cloudflare R2. For post media we also keep the original filename, file type, and size. Media attached to a public post or profile is presented publicly through time-limited delivery URLs. A GitHub-provided avatar remains hosted by GitHub unless you replace it.</td>
                            </tr>
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">Safety and service logs</th>
                                <td class="px-5 py-4">Reports, selected reported messages, moderation decisions, content-request details, IP address, device and browser information, session and security events, rate-limit events, and records needed to investigate faults or abuse.</td>
                            </tr>
                            <tr>
                                <th class="px-5 py-4 align-top font-medium text-zinc-800 dark:text-zinc-200">Automated discovery</th>
                                <td class="px-5 py-4">Tavily receives a search query and approved source domains. The integration is configured not to request raw page content. Azure OpenAI receives returned source metadata and short snippets. Member account data is not intentionally sent through the curation agent. Provider retention and training restrictions must be confirmed in the operator's contracts and production settings.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">3. Why we use it</h2>
                <div class="grid gap-4 text-sm leading-7 text-zinc-600 dark:text-zinc-400 sm:grid-cols-2">
                    <div><h3 class="font-semibold text-zinc-800 dark:text-zinc-200">Provide the service</h3><p>Authenticate accounts, record eligibility and Terms acceptance, publish profiles and contributions, store media, maintain follows, deliver messages, and apply settings. The UK/EU basis is normally performance of the contract you request by joining and using member features.</p></div>
                    <div><h3 class="font-semibold text-zinc-800 dark:text-zinc-200">Protect the community</h3><p>Prevent spam and fraud, secure accounts, investigate reports, enforce rules, and preserve proportionate evidence. The basis is the operator's legitimate interests in a safe, reliable service, balanced against people's rights and reasonable expectations.</p></div>
                    <div><h3 class="font-semibold text-zinc-800 dark:text-zinc-200">Meet legal duties</h3><p>Respond to valid legal process, safety reports, data-rights requests, and legally required removals or disclosures. The basis is legal obligation where one applies.</p></div>
                    <div><h3 class="font-semibold text-zinc-800 dark:text-zinc-200">Optional uses</h3><p>Optional analytics, advertising, or marketing are not part of the current product. They must not be enabled without updating this notice and obtaining consent or offering an opt-out where law requires.</p></div>
                </div>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    A verified email, account identifier, confirmation that you are at least {{ $legal['minimum_age'] }}, and acceptance of the current Terms are required to provide an account. Profile details beyond a name and username, public contributions, follows, media, and direct messages are optional. Laraloom does not use automated processing to make decisions that produce legal or similarly significant effects. Automated curation and abuse controls may rank or flag material, but final account and legal-content decisions require authorized human review.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Public contributions can incidentally reveal health, beliefs, sexuality, ethnicity, politics, trade-union membership, biometric or genetic information, or allegations about offences. Laraloom does not ask members to provide those details, infer them for advertising, or build sensitive profiles. Do not publish another person's sensitive or criminal-offence information without a lawful basis. The operator must identify an applicable UK/EU Article 9 or Article 10 condition before deliberately using such information beyond displaying content at the member's direction, or remove or restrict it where no condition applies.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">4. What is public and what is private</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Names, usernames, biographies, profile images, follower and following lists, posts, projects, replies, reactions, and reposts are public by design. Public information can be copied or indexed by others. Bookmarks, account-security settings, and direct-message conversations are not shown publicly.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">5. Direct-message privacy and encryption</h2>
                <div class="rounded-2xl bg-zinc-100/80 px-5 py-4 text-sm leading-7 text-zinc-700 dark:bg-white/[.045] dark:text-zinc-300">
                    Message bodies are encrypted over HTTPS and while stored with keys controlled by the service. They are <strong class="font-semibold text-zinc-950 dark:text-white">not end-to-end encrypted</strong>. Vask processes connection and delivery-event metadata containing conversation, message, sender, and timestamp identifiers; it does not receive decrypted message bodies. The authorized participant fetches a body over HTTPS.
                </div>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Administrators cannot ordinarily open or search conversations and have no blanket moderation bypass. The operator does not proactively read messages. If a participant reports a conversation, the selected messages and related identifiers are deliberately disclosed to authorized safety staff. Information may also be disclosed when valid law requires it or an urgent threat to life or safety must be addressed.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Messages are not disappearing and the current product has no per-message delete-for-everyone control. While both accounts remain, history stays visible to both participants even if they no longer follow one another. Deleting either participant's account removes the live conversation history from the primary database; backups and limited safety or legal records follow the configured retention schedule.
                </p>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">6. When information is shared</h2>
                <ul class="list-disc space-y-2 pl-5 text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    <li><strong class="font-medium text-zinc-800 dark:text-zinc-200">The public and other members:</strong> public community information is visible to anyone; message content is delivered only to conversation participants unless reported.</li>
                    <li><strong class="font-medium text-zinc-800 dark:text-zinc-200">GitHub:</strong> provides OAuth sign-in and may continue to host the initial avatar under GitHub's own privacy terms.</li>
                    <li><strong class="font-medium text-zinc-800 dark:text-zinc-200">Cloudflare R2:</strong> stores private media objects used for public posts and user-uploaded profile photos.</li>
                    <li><strong class="font-medium text-zinc-800 dark:text-zinc-200">Vask:</strong> processes WebSocket connection data and metadata-only real-time events.</li>
                    <li><strong class="font-medium text-zinc-800 dark:text-zinc-200">Curation providers:</strong> Tavily and Azure OpenAI process source searches, metadata, and short snippets—not member account data—for automated discovery.</li>
                    <li><strong class="font-medium text-zinc-800 dark:text-zinc-200">Other processors:</strong> {{ $legal['additional_processors'] ?: 'Hosting, database, email, monitoring, and other production processors have not yet been configured in this notice.' }}</li>
                    <li><strong class="font-medium text-zinc-800 dark:text-zinc-200">Legal and safety recipients:</strong> information is shared when required by valid law, to protect rights and safety, or in a properly governed business transfer.</li>
                </ul>
                <p class="text-xs leading-5 text-zinc-500">Processors must be contractually limited, kept confidential, secured, and required to help with rights, incidents, deletion, and audits. The operator must keep its actual list current.</p>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">7. Retention</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">We keep identifiable information only for a documented purpose. The production operator must replace every unconfigured entry below with a specific period or clear deletion criterion.</p>
                <dl class="grid gap-x-8 gap-y-4 rounded-2xl bg-zinc-100/80 p-5 text-sm dark:bg-white/[.045] sm:grid-cols-[14rem_1fr]">
                    <dt class="font-medium text-zinc-800 dark:text-zinc-200">Accounts and public content</dt><dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['retention']['accounts_and_public_content'] ?: 'Not configured — required before launch.' }}</dd>
                    <dt class="font-medium text-zinc-800 dark:text-zinc-200">Direct messages</dt><dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['retention']['direct_messages'] ?: 'Not configured — required before launch.' }}</dd>
                    <dt class="font-medium text-zinc-800 dark:text-zinc-200">Security logs</dt><dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['retention']['security_logs'] ?: 'Not configured — required before launch.' }}</dd>
                    <dt class="font-medium text-zinc-800 dark:text-zinc-200">Moderation and reports</dt><dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['retention']['moderation_and_reports'] ?: 'Not configured — required before launch.' }}</dd>
                    <dt class="font-medium text-zinc-800 dark:text-zinc-200">Backups</dt><dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['retention']['backups'] ?: 'Not configured — required before launch.' }}</dd>
                </dl>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">8. Your privacy rights</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Depending on where you live, you may ask to access, correct, delete, restrict, or receive a portable copy of your information; object to processing; withdraw consent for optional processing; or appeal a refusal. These rights are not absolute, and another participant's rights may affect a message export or deletion request. Requests are free unless law permits a fee for a manifestly unfounded or excessive request.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Use the <a class="font-medium text-rose-600 hover:underline dark:text-[#ff7693]" href="{{ route('legal.content-request') }}">content and rights request form</a> or the privacy email below. We may verify your identity proportionately. Before serving UK or EEA users, the operator must be able to answer rights requests without undue delay and generally within one month. UK data-protection complaints must be acknowledged within 30 days, investigated without undue delay with appropriate updates, and closed with an outcome notice.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    You may also complain to the regulator where you live, including the
                    <a class="font-medium text-rose-600 hover:underline dark:text-[#ff7693]" href="https://ico.org.uk/make-a-complaint/" rel="noreferrer">UK ICO</a>,
                    an <a class="font-medium text-rose-600 hover:underline dark:text-[#ff7693]" href="https://www.edpb.europa.eu/about-edpb/about-edpb/members_en" rel="noreferrer">EEA supervisory authority</a>, or the
                    <a class="font-medium text-rose-600 hover:underline dark:text-[#ff7693]" href="https://cppa.ca.gov/webapplications/complaint" rel="noreferrer">California Privacy Protection Agency</a> where applicable.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">9. Cookies and local storage</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    The current service uses storage needed for authentication, security, sessions, CSRF protection, and your light or dark appearance preference. It does not currently use advertising or optional analytics cookies. Optional storage must remain off until a clear choice is offered; refusing it must not block core features, and consent must be as easy to withdraw as to give.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">10. California disclosures</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    The categories, sources, purposes, recipients, retention information, and controls required for an online privacy notice are described above. The current product does not sell personal information or share it for cross-context behavioural advertising and does not offer financial incentives for personal information.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    We do not respond to the legacy browser “Do Not Track” signal because the product does not perform cross-site advertising tracking. If a future deployment sells or shares information, it must add a “Do Not Sell or Share” control and honor valid opt-out preference signals such as Global Privacy Control before that processing begins. If the CCPA applies to the operator, California residents also have rights to know, delete, correct, limit qualifying sensitive-information uses, opt out of sale or sharing, and receive equal service.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">11. Children</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Laraloom is only for people aged {{ $legal['minimum_age'] }} or over and does not offer a parental-consent route for younger users. The acceptance flow records a self-declaration that the threshold is met without collecting a date of birth. If the operator learns that an account belongs to someone under {{ $legal['minimum_age'] }}, it will restrict the account and delete or anonymize the person's information unless a limited record must be kept for safety or law. A parent, guardian, or other person can report a suspected underage account through the <a class="font-medium text-rose-600 hover:underline dark:text-[#ff7693]" href="{{ route('legal.content-request') }}">rights and safety form</a>. The operator must assess whether children are nevertheless likely to access the service and introduce proportionate age assurance and age-appropriate safeguards if self-declaration is not adequate.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">12. Security, transfers, and incidents</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    We use access controls, private object storage, transport encryption, encrypted-at-rest message bodies, and restricted administrative tools. No system is perfectly secure. The operator must maintain incident-response and breach-notification procedures appropriate to the risk.
                </p>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Providers may process information outside your country. Where UK or EEA transfer restrictions apply, the operator must use an adequacy decision or an approved safeguard such as EU Standard Contractual Clauses, the UK International Data Transfer Agreement, or the UK Addendum, and explain how to obtain a copy. EU representative: {{ $legal['representatives']['eu'] ?: 'not listed; configure one if legally required' }}. UK representative: {{ $legal['representatives']['uk'] ?: 'not listed; configure one if legally required' }}.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-zinc-100">13. Changes and contact</h2>
                <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    We will update the version and effective date and give clear notice before a material new use begins. Earlier information is not repurposed incompatibly without a valid legal basis and any consent that law requires. Updating this notice does not convert it into consent; if optional processing legally requires consent, Laraloom will ask separately with a real choice.
                </p>
                <dl class="grid gap-3 rounded-2xl bg-zinc-100/80 p-5 text-sm dark:bg-white/[.045] sm:grid-cols-[10rem_1fr]">
                    <dt class="font-medium text-zinc-700 dark:text-zinc-300">Operator</dt><dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['operator']['name'] ?: 'Not configured' }}</dd>
                    <dt class="font-medium text-zinc-700 dark:text-zinc-300">Country</dt><dd class="text-zinc-600 dark:text-zinc-400">{{ $legal['operator']['country'] ?: 'Not configured' }}</dd>
                    <dt class="font-medium text-zinc-700 dark:text-zinc-300">Postal address</dt><dd class="whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $legal['operator']['postal_address'] ?: 'Not configured' }}</dd>
                    <dt class="font-medium text-zinc-700 dark:text-zinc-300">Privacy contact</dt>
                    <dd class="text-zinc-600 dark:text-zinc-400">
                        @if (filled($legal['operator']['privacy_email']))
                            <a class="text-rose-600 hover:underline dark:text-[#ff7693]" href="mailto:{{ $legal['operator']['privacy_email'] }}">{{ $legal['operator']['privacy_email'] }}</a>
                        @else
                            Not configured
                        @endif
                    </dd>
                </dl>
            </section>
        </div>
    </article>
@endsection

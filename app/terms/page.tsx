import Link from "next/link";

export const metadata = {
  title: "Terms of Service | Codentra",
  description: "Our terms and conditions for using Codentra services.",
};

export default function TermsPage() {
  return (
    <main className="bg-background text-text-primary">
      {/* Navigation */}
      <nav className="sticky top-0 z-50 bg-background/80 backdrop-blur-md border-b border-border px-6 py-4">
        <div className="max-w-4xl mx-auto flex justify-between items-center">
          <Link href="/" className="text-xl font-bold text-primary">Codentra</Link>
          <Link href="/" className="text-text-secondary hover:text-text-primary transition-colors">← Back to Home</Link>
        </div>
      </nav>

      {/* Content */}
      <div className="px-6 py-24">
        <div className="max-w-4xl mx-auto prose prose-invert">
          <h1 className="text-4xl font-bold mb-8">Terms of Service</h1>
          <p className="text-text-secondary text-lg mb-8">Last updated: April 25, 2026</p>

          <section className="space-y-12">
            <div>
              <h2 className="text-2xl font-semibold mb-4">1. Acceptance of Terms</h2>
              <p className="text-text-secondary leading-relaxed">
                By accessing and using Codentra's services, you agree to be bound by these Terms of Service. 
                If you do not agree to abide by the above, please do not use this service.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">2. Use License</h2>
              <p className="text-text-secondary leading-relaxed">
                You are granted a non-exclusive, non-transferable, revocable license to access and use 
                Codentra services for legitimate business purposes only, in accordance with these terms.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">3. Disclaimer of Warranties</h2>
              <p className="text-text-secondary leading-relaxed">
                Our services are provided on an "AS IS" and "AS AVAILABLE" basis. We make no warranties, 
                express or implied, regarding the service and disclaim all warranties, including any implied 
                warranties of merchantability and fitness for a particular purpose.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">4. Limitation of Liability</h2>
              <p className="text-text-secondary leading-relaxed">
                To the fullest extent permitted by law, Codentra shall not be liable for any indirect, 
                incidental, special, consequential, or punitive damages resulting from your use of or 
                inability to use our services.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">5. Prohibited Activities</h2>
              <p className="text-text-secondary leading-relaxed mb-4">You agree not to:</p>
              <ul className="list-disc list-inside text-text-secondary space-y-2 pl-4">
                <li>Violate any applicable laws or regulations</li>
                <li>Infringe on intellectual property rights</li>
                <li>Transmit malware or harmful code</li>
                <li>Attempt to gain unauthorized access</li>
                <li>Harass, abuse, or harm others</li>
                <li>Reverse engineer or decompile our services</li>
              </ul>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">6. Intellectual Property Rights</h2>
              <p className="text-text-secondary leading-relaxed">
                All content, features, and functionality (including but not limited to all information, 
                software, text, displays, images, video and audio) are the exclusive property of Codentra 
                or its content suppliers and are protected by international copyright laws.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">7. User Accounts</h2>
              <p className="text-text-secondary leading-relaxed">
                You are responsible for maintaining the confidentiality of your account information and password. 
                You agree to accept responsibility for all activity that occurs under your account. 
                You must notify us immediately of any unauthorized use of your account.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">8. Modification of Service</h2>
              <p className="text-text-secondary leading-relaxed">
                Codentra reserves the right to modify, suspend, or discontinue its services at any time 
                and for any reason. We will provide notice of major changes where practicable.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">9. Termination</h2>
              <p className="text-text-secondary leading-relaxed">
                We reserve the right to terminate or suspend your account and access to services immediately, 
                without prior notice or liability, if you violate these Terms or engage in conduct we 
                deem harmful to our platform or users.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">10. Governing Law</h2>
              <p className="text-text-secondary leading-relaxed">
                These Terms are governed by and construed in accordance with the laws of Pakistan, 
                and you irrevocably submit to the exclusive jurisdiction of the courts located there.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">11. Contact Information</h2>
              <p className="text-text-secondary leading-relaxed">
                If you have any questions about these Terms, please contact us at:
              </p>
              <p className="text-text-secondary mt-4">
                Email: legal@codentra.pk<br />
                Address: [Your Business Address]
              </p>
            </div>
          </section>

          <div className="mt-16 p-8 rounded-lg bg-card border border-border/50">
            <p className="text-text-secondary">
              We may update these Terms at any time. Your continued use of our services after any 
              modifications constitutes your acceptance of the updated Terms.
            </p>
          </div>
        </div>
      </div>
    </main>
  );
}

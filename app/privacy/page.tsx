import Link from "next/link";

export const metadata = {
  title: "Privacy Policy | Codentra",
  description: "Our commitment to your privacy and data protection.",
};

export default function PrivacyPage() {
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
          <h1 className="text-4xl font-bold mb-8">Privacy Policy</h1>
          <p className="text-text-secondary text-lg mb-8">Last updated: April 25, 2026</p>

          <section className="space-y-12">
            <div>
              <h2 className="text-2xl font-semibold mb-4">1. Introduction</h2>
              <p className="text-text-secondary leading-relaxed">
                At Codentra, we respect your privacy and are committed to protecting your personal data. 
                This Privacy Policy explains how we collect, use, disclose, and safeguard your information.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">2. Information We Collect</h2>
              <p className="text-text-secondary leading-relaxed mb-4">
                We collect information you provide directly, such as:
              </p>
              <ul className="list-disc list-inside text-text-secondary space-y-2 pl-4">
                <li>Contact information (name, email, phone)</li>
                <li>Account information (username, password)</li>
                <li>Communication data (messages, support tickets)</li>
                <li>Payment information (processed securely)</li>
              </ul>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">3. How We Use Your Information</h2>
              <p className="text-text-secondary leading-relaxed mb-4">
                We use your information to:
              </p>
              <ul className="list-disc list-inside text-text-secondary space-y-2 pl-4">
                <li>Provide and improve our services</li>
                <li>Communicate with you about your account</li>
                <li>Send marketing communications (with your consent)</li>
                <li>Comply with legal obligations</li>
                <li>Prevent fraud and ensure security</li>
              </ul>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">4. Data Security</h2>
              <p className="text-text-secondary leading-relaxed">
                We implement industry-standard security measures including SSL encryption, 
                secure data centers, and regular security audits to protect your personal data 
                from unauthorized access, alteration, disclosure, or destruction.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">5. Your Rights</h2>
              <p className="text-text-secondary leading-relaxed mb-4">
                Depending on your location, you may have rights including:
              </p>
              <ul className="list-disc list-inside text-text-secondary space-y-2 pl-4">
                <li>Right to access your personal data</li>
                <li>Right to correct inaccurate data</li>
                <li>Right to delete your data</li>
                <li>Right to opt-out of communications</li>
              </ul>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">6. Cookies</h2>
              <p className="text-text-secondary leading-relaxed">
                We use cookies to enhance your experience and understand how you use our platform. 
                You can control cookie settings through your browser preferences.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">7. Third-Party Services</h2>
              <p className="text-text-secondary leading-relaxed">
                We may share data with trusted third parties (payment processors, analytics providers) 
                who are bound by confidentiality agreements and comply with applicable privacy laws.
              </p>
            </div>

            <div>
              <h2 className="text-2xl font-semibold mb-4">8. Contact Us</h2>
              <p className="text-text-secondary leading-relaxed">
                If you have privacy concerns or requests, contact us at:
              </p>
              <p className="text-text-secondary mt-4">
                Email: privacy@codentra.pk<br />
                Address: [Your Business Address]
              </p>
            </div>
          </section>

          <div className="mt-16 p-8 rounded-lg bg-card border border-border/50">
            <p className="text-text-secondary">
              We may update this Privacy Policy periodically. Continued use of our services 
              constitutes acceptance of the updated policy.
            </p>
          </div>
        </div>
      </div>
    </main>
  );
}

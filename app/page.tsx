import Link from "next/link";

export default function Home() {
  return (
    <main className="bg-background text-text-primary">
      {/* Navigation */}
      <nav className="fixed top-0 left-0 right-0 z-50 bg-background/80 backdrop-blur-md border-b border-border px-6 py-4">
        <div className="max-w-7xl mx-auto flex justify-between items-center">
          <div className="text-xl font-bold text-primary">Codentra</div>
          <div className="flex gap-8">
            <Link href="#services" className="text-text-secondary hover:text-text-primary transition-colors">Services</Link>
            <Link href="#features" className="text-text-secondary hover:text-text-primary transition-colors">Why Us</Link>
            <Link href="#contact" className="text-text-secondary hover:text-text-primary transition-colors">Contact</Link>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="min-h-screen flex items-center justify-center px-6 pt-20">
        <div className="max-w-4xl mx-auto text-center space-y-8">
          <div className="inline-block px-4 py-2 bg-primary/10 rounded-lg border border-primary/20">
            <p className="text-sm text-primary font-medium">Modern Tech Provider</p>
          </div>
          
          <h1 className="text-5xl md:text-6xl font-bold leading-tight">
            <span className="text-gradient">Code.</span> Automate. <span className="text-gradient">Scale.</span>
          </h1>
          
          <p className="text-xl text-text-secondary max-w-2xl mx-auto leading-relaxed">
            Enterprise-grade technology solutions designed for businesses that want to move fast, 
            stay lean, and dominate their market.
          </p>
          
          <div className="flex flex-col sm:flex-row gap-4 justify-center pt-8">
            <button className="px-8 py-4 bg-primary hover:bg-primary-dark rounded-lg text-white font-semibold transition-colors">
              Get Started
            </button>
            <button className="px-8 py-4 border border-primary text-primary hover:bg-primary/10 rounded-lg font-semibold transition-colors">
              Learn More
            </button>
          </div>
        </div>
      </section>

      {/* Problem/Value Section */}
      <section className="py-24 px-6 bg-surface/50">
        <div className="max-w-4xl mx-auto">
          <h2 className="text-4xl font-bold mb-16 text-center">Why Codentra</h2>
          
          <div className="grid md:grid-cols-3 gap-12">
            {/* Card 1 */}
            <div className="p-8 rounded-lg bg-card border border-border/50 hover:border-border transition-colors">
              <div className="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center mb-6">
                <span className="text-primary text-xl">⚡</span>
              </div>
              <h3 className="text-xl font-semibold mb-4">Lightning Fast</h3>
              <p className="text-text-secondary">
                Optimized systems that deliver results in milliseconds, not seconds. 
                Speed is not a feature—it's a requirement.
              </p>
            </div>

            {/* Card 2 */}
            <div className="p-8 rounded-lg bg-card border border-border/50 hover:border-border transition-colors">
              <div className="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center mb-6">
                <span className="text-primary text-xl">🔧</span>
              </div>
              <h3 className="text-xl font-semibold mb-4">Fully Automated</h3>
              <p className="text-text-secondary">
                Remove manual tasks from your workflow. Our solutions automate 
                the tedious so you can focus on strategy.
              </p>
            </div>

            {/* Card 3 */}
            <div className="p-8 rounded-lg bg-card border border-border/50 hover:border-border transition-colors">
              <div className="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center mb-6">
                <span className="text-primary text-xl">📈</span>
              </div>
              <h3 className="text-xl font-semibold mb-4">Built to Scale</h3>
              <p className="text-text-secondary">
                From startup to enterprise. Our architecture grows with your 
                business without compromise.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Services Section */}
      <section id="services" className="py-24 px-6">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-4xl font-bold mb-16 text-center">Our Services</h2>
          
          <div className="grid md:grid-cols-2 gap-12">
            {/* Service 1 */}
            <div className="space-y-4">
              <div className="flex items-start gap-4">
                <div className="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                  <span className="text-primary">01</span>
                </div>
                <div>
                  <h3 className="text-2xl font-semibold mb-3">Custom Development</h3>
                  <p className="text-text-secondary">
                    Bespoke solutions built from the ground up for your unique needs. 
                    Production-grade code that scales.
                  </p>
                </div>
              </div>
            </div>

            {/* Service 2 */}
            <div className="space-y-4">
              <div className="flex items-start gap-4">
                <div className="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                  <span className="text-primary">02</span>
                </div>
                <div>
                  <h3 className="text-2xl font-semibold mb-3">Process Automation</h3>
                  <p className="text-text-secondary">
                    Transform manual workflows into intelligent, automated systems. 
                    Save hours every week.
                  </p>
                </div>
              </div>
            </div>

            {/* Service 3 */}
            <div className="space-y-4">
              <div className="flex items-start gap-4">
                <div className="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                  <span className="text-primary">03</span>
                </div>
                <div>
                  <h3 className="text-2xl font-semibold mb-3">System Integration</h3>
                  <p className="text-text-secondary">
                    Connect all your tools seamlessly. Eliminate data silos. 
                    One unified platform.
                  </p>
                </div>
              </div>
            </div>

            {/* Service 4 */}
            <div className="space-y-4">
              <div className="flex items-start gap-4">
                <div className="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                  <span className="text-primary">04</span>
                </div>
                <div>
                  <h3 className="text-2xl font-semibold mb-3">Infrastructure & Scaling</h3>
                  <p className="text-text-secondary">
                    Enterprise infrastructure that grows with you. 
                    99.9% uptime guaranteed.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Proof/Case Studies Section */}
      <section id="features" className="py-24 px-6 bg-surface/50">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-4xl font-bold mb-16 text-center">Trusted by Leading Brands</h2>
          
          <div className="grid md:grid-cols-2 gap-12">
            {/* Case Study 1 */}
            <div className="p-12 rounded-lg bg-card border border-border/50">
              <div className="flex items-center gap-4 mb-6">
                <div className="w-12 h-12 rounded-full bg-primary/20"></div>
                <div>
                  <p className="font-semibold">TechFlow Co.</p>
                  <p className="text-sm text-text-secondary">SaaS Startup</p>
                </div>
              </div>
              <p className="text-lg mb-6">
                "Codentra automated our entire order pipeline. We cut processing time by 80% 
                in just 6 weeks."
              </p>
              <p className="text-success text-sm font-semibold">+80% Efficiency • 6 weeks</p>
            </div>

            {/* Case Study 2 */}
            <div className="p-12 rounded-lg bg-card border border-border/50">
              <div className="flex items-center gap-4 mb-6">
                <div className="w-12 h-12 rounded-full bg-primary/20"></div>
                <div>
                  <p className="font-semibold">Scale Logistics</p>
                  <p className="text-sm text-text-secondary">Enterprise</p>
                </div>
              </div>
              <p className="text-lg mb-6">
                "Their integration solution unified our 5 separate systems into one platform. 
                The ROI was immediate."
              </p>
              <p className="text-success text-sm font-semibold">5→1 Platform • Immediate ROI</p>
            </div>
          </div>
        </div>
      </section>

      {/* Final CTA Section */}
      <section id="contact" className="py-24 px-6">
        <div className="max-w-4xl mx-auto text-center space-y-8">
          <h2 className="text-4xl font-bold">Ready to Transform Your Business?</h2>
          <p className="text-xl text-text-secondary max-w-2xl mx-auto">
            Join the brands that are automating, scaling, and winning. 
            Let's build something remarkable together.
          </p>
          
          <div className="flex flex-col sm:flex-row gap-4 justify-center pt-8">
            <button className="px-8 py-4 bg-primary hover:bg-primary-dark rounded-lg text-white font-semibold transition-colors">
              Schedule Consultation
            </button>
            <button className="px-8 py-4 border border-primary text-primary hover:bg-primary/10 rounded-lg font-semibold transition-colors">
              View Pricing
            </button>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-border/30 py-12 px-6 bg-surface/30">
        <div className="max-w-6xl mx-auto">
          <div className="grid md:grid-cols-4 gap-12 mb-12">
            <div>
              <p className="text-primary font-bold text-lg mb-4">Codentra</p>
              <p className="text-text-secondary text-sm">Code. Automate. Scale.</p>
            </div>
            <div>
              <p className="font-semibold mb-4">Services</p>
              <ul className="space-y-2 text-sm text-text-secondary">
                <li><Link href="#" className="hover:text-text-primary">Development</Link></li>
                <li><Link href="#" className="hover:text-text-primary">Automation</Link></li>
                <li><Link href="#" className="hover:text-text-primary">Integration</Link></li>
              </ul>
            </div>
            <div>
              <p className="font-semibold mb-4">Company</p>
              <ul className="space-y-2 text-sm text-text-secondary">
                <li><Link href="#" className="hover:text-text-primary">About</Link></li>
                <li><Link href="#" className="hover:text-text-primary">Blog</Link></li>
                <li><Link href="#" className="hover:text-text-primary">Contact</Link></li>
              </ul>
            </div>
            <div>
              <p className="font-semibold mb-4">Legal</p>
              <ul className="space-y-2 text-sm text-text-secondary">
                <li><Link href="/privacy" className="hover:text-text-primary">Privacy</Link></li>
                <li><Link href="/terms" className="hover:text-text-primary">Terms</Link></li>
              </ul>
            </div>
          </div>
          
          <div className="border-t border-border/30 pt-8 flex justify-between items-center text-sm text-text-secondary">
            <p>&copy; 2026 Codentra. All rights reserved.</p>
            <div className="flex gap-6">
              <Link href="#" className="hover:text-text-primary">Twitter</Link>
              <Link href="#" className="hover:text-text-primary">LinkedIn</Link>
              <Link href="#" className="hover:text-text-primary">GitHub</Link>
            </div>
          </div>
        </div>
      </footer>

      <style jsx>{`
        .text-gradient {
          background: linear-gradient(135deg, #4F46E5 0%, #818CF8 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
        }
      `}</style>
    </main>
  );
}

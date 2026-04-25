import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Codentra - Modern Tech Solutions",
  description: "Code. Automate. Scale. Premium SaaS-style tech provider for modern businesses.",
  viewport: "width=device-width, initial-scale=1",
  keywords: ["tech solutions", "automation", "SaaS", "development"],
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className="bg-background text-text-primary">
        {children}
      </body>
    </html>
  );
}

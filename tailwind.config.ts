import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        background: "#0B0F19",
        surface: "#111827",
        card: "#1F2937",
        border: "#2D3748",
        primary: {
          DEFAULT: "#4F46E5",
          dark: "#3730A3",
          light: "#818CF8",
        },
        text: {
          primary: "#F9FAFB",
          secondary: "#9CA3AF",
          muted: "#6B7280",
        },
        success: "#10B981",
        warning: "#F59E0B",
        error: "#EF4444",
        info: "#3B82F6",
      },
      spacing: {
        xs: "4px",
        sm: "8px",
        md: "16px",
        lg: "24px",
        xl: "32px",
        "2xl": "48px",
        "3xl": "64px",
        "4xl": "96px",
      },
      fontSize: {
        "h1": "48px",
        "h2": "32px",
        "h3": "24px",
        "body": "16px",
        "small": "14px",
        "caption": "12px",
      },
      lineHeight: {
        tight: "1.1",
        heading: "1.3",
        body: "1.6",
        relaxed: "1.8",
      },
      borderRadius: {
        sm: "8px",
        md: "12px",
        lg: "16px",
      },
      maxWidth: {
        "content": "1200px",
      },
    },
  },
  plugins: [],
};
export default config;

"use client"

export default function WordPressThemeNotice() {
  return (
    <div
      style={{
        padding: "2rem",
        maxWidth: "800px",
        margin: "0 auto",
        fontFamily: "system-ui, sans-serif",
      }}
    >
      <h1 style={{ fontSize: "2rem", fontWeight: "bold", marginBottom: "1rem" }}>WordPress Theme - Khoshtip Kocholo</h1>

      <div
        style={{
          backgroundColor: "#f0f9ff",
          border: "1px solid #0ea5e9",
          borderRadius: "8px",
          padding: "1.5rem",
          marginBottom: "1.5rem",
        }}
      >
        <h2 style={{ fontSize: "1.25rem", fontWeight: "600", marginBottom: "0.5rem" }}>ℹ️ This is a WordPress Theme</h2>
        <p style={{ marginBottom: "1rem" }}>
          This project is a WordPress theme and cannot be previewed in v0's Next.js environment.
        </p>
        <p style={{ fontWeight: "500" }}>To use this theme:</p>
        <ol style={{ marginLeft: "1.5rem", marginTop: "0.5rem" }}>
          <li>Download the ZIP file using the three dots menu (top right)</li>
          <li>
            Upload to your WordPress site's{" "}
            <code
              style={{
                backgroundColor: "#e5e7eb",
                padding: "0.125rem 0.375rem",
                borderRadius: "4px",
              }}
            >
              wp-content/themes/
            </code>{" "}
            directory
          </li>
          <li>Activate the theme in WordPress admin</li>
        </ol>
      </div>

      <div
        style={{
          backgroundColor: "#f0fdf4",
          border: "1px solid #22c55e",
          borderRadius: "8px",
          padding: "1.5rem",
        }}
      >
        <h2 style={{ fontSize: "1.25rem", fontWeight: "600", marginBottom: "0.5rem", color: "#16a34a" }}>
          ✅ Recent Improvements Made
        </h2>
        <ul style={{ marginLeft: "1.5rem", lineHeight: "1.75" }}>
          <li>
            <strong>Banner:</strong> Removed all text overlays, entire banner is now clickable
          </li>
          <li>
            <strong>Product Accordion:</strong> Fixed initial load bug, only one accordion opens at a time
          </li>
          <li>
            <strong>Stories:</strong> White background with animated gradient rings (Instagram-style)
          </li>
          <li>
            <strong>Performance:</strong> Optimized animations with GPU acceleration
          </li>
        </ul>
      </div>

      <div style={{ marginTop: "2rem", padding: "1rem", backgroundColor: "#fef2f2", borderRadius: "8px" }}>
        <p style={{ fontSize: "0.875rem", color: "#991b1b" }}>
          <strong>Note:</strong> Make sure you have WooCommerce installed and activated for the product features to work
          properly.
        </p>
      </div>
    </div>
  )
}

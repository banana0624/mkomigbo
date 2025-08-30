// project-root/layouts/aboutLayout.tsx

import React from 'react'

type AboutLayoutProps = {
  title: string
  children: React.ReactNode
}

const AboutLayout: React.FC<AboutLayoutProps> = ({ title, children }) => (
  <section className="about-layout">
    <header>
      <h1>{title}</h1>
    </header>
    <main>{children}</main>
  </section>
)

export default AboutLayout
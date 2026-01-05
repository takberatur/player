<template>
  <div class="prose prose-slate dark:prose-invert max-w-none">
    <h1>Forge Player Documentation: Introduction</h1>
    <p>
      Forge Player is a modern video streaming application built with Laravel,
      Inertia.js, and Vue 3. It focuses on a seamless playback experience,
      robust subtitle delivery, and integrated advertising with detailed
      analytics for performance tracking.
    </p>

    <h2>Core Capabilities</h2>
    <ul>
      <li>JW Player integration supporting HLS and DASH sources.</li>
      <li>Subtitle delivery with correct MIME types for SRT and VTT.</li>
      <li>
        Advertising via VAST tags with pre-, mid-, and post-roll scheduling.
      </li>
      <li>Ad analytics covering impressions, clicks, start, and complete.</li>
      <li>Single-Page App experience powered by Inertia.js and Vue 3.</li>
    </ul>

    <h2>Architecture Overview</h2>
    <p>
      The backend is implemented in Laravel, providing REST endpoints,
      Inertia-powered pages, and middleware-based access control. The frontend
      uses Vue 3 components for player UI, documentation pages, and admin
      dashboards. Data is shared to the client through Inertia's shared props.
    </p>

    <h2>Advertising & Analytics</h2>
    <p>
      VAST tags can be configured for pre-roll and periodic mid-roll schedules,
      with optional post-roll. The system records key metrics to help analyze ad
      performance. Admin dashboards visualize trends across impressions, clicks,
      starts, and completes for each tag.
    </p>

    <h2>Subtitle Handling</h2>
    <p>
      Subtitles are served from the public storage using a dedicated route and
      controller that returns inline content with the appropriate MIME type,
      ensuring the player can render captions directly without forcing a
      download. Player-side logic transforms database URLs into routable,
      origin-aware links.
    </p>

    <h2>Security & Permissions</h2>
    <ul>
      <li>
        Middleware restricts non-GET operations to admin users, returning a
        global error for unauthorized actions.
      </li>
      <li>
        Sensitive data is not logged, and headers are set to support CORS
        safely.
      </li>
    </ul>

    <h2>Deployment</h2>
    <p>
      The project supports traditional Nginx/PHP-FPM deployments and Dockerized
      setups. Build steps include Composer for PHP dependencies and Vite for
      frontend assets. Environment configuration enables production-grade
      caching and optimization.
    </p>

    <h2>Development Workflow</h2>
    <ul>
      <li>Install PHP and Node dependencies with Composer and npm.</li>
      <li>Run migrations and seed data as needed.</li>
      <li>Use Inertia for page rendering and Vue components for UI.</li>
      <li>Leverage JW Player APIs for media playback and captions.</li>
    </ul>

    <h2>Key Modules</h2>
    <ul>
      <li>Client player page with JW Player setup and caption tracks.</li>
      <li>Subtitle controller and routing for inline caption delivery.</li>
      <li>Admin analytics views for VAST tag performance monitoring.</li>
      <li>Documentation pages organized into sections and subsections.</li>
    </ul>

    <h2>Goals</h2>
    <p>
      Provide a clean, reliable player experience with actionable insights for
      advertising, while keeping the codebase maintainable and extensible for
      future features.
    </p>
  </div>
</template>

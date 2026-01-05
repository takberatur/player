import axios from "axios";
import VideoController from "@/actions/App/Http/Controllers/Client/VideoController";
import { type VideoSource } from "./useYoutubeDownloader";
import { useRequestVideo } from "./useRequestVideo";
import { ref } from "vue";

export type RumbleResult = {
  success: boolean;
  sources?: VideoSource[];
  error?: string;
  jsonString?: string;
};

export const useRumbleDownloader = () => {
  const { request } = useRequestVideo()

  const QUALITY_MAP = ref({
    360: "360p",
    480: "480p",
    720: "720p",
    1080: "1080p",
    1440: "1440p",
    2160: "2160p",
  })

  const buildHeaders = (link: string, kind: "page" | "api") => {
    const cookie = process.env.RUMBLE_COOKIE;
    const base: Record<string, string> = {
      "User-Agent":
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
      "Accept-Language": "en-US,en;q=0.9",
      "Sec-CH-UA":
        '"Google Chrome";v="130", "Chromium";v="130", "Not;A=Brand";v="99"',
      "Sec-CH-UA-Platform": '"Windows"',
      "Sec-CH-UA-Mobile": "?0",
    };
    if (kind === "page") {
      base["Accept"] =
        "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
      base["Upgrade-Insecure-Requests"] = "1";
    } else {
      base["Origin"] = "https://rumble.com";
      base["Referer"] = link;
      base["Accept"] = "*/*";
      base["Sec-Fetch-Site"] = "same-origin";
      base["Sec-Fetch-Mode"] = "cors";
      base["Sec-Fetch-Dest"] = "empty";
    }
    if (cookie) base["Cookie"] = cookie;
    return base;
  }
  const extractVideoIdFromUrl = (url: string): string | null => {
    const match = url.match(/rumble\.com\/(v[0-9a-z]+)-/i);
    return match && match[1] ? match[1] : null;
  }
  const extractVideoId = (html: string): string | null => {
    const match = html.match(/"video":"(.*?)"/);
    return match && match[1] ? match[1] : null;
  }
  const getVideoSources = async (link: string): Promise<RumbleResult> => {
    try {
      if (!link.includes("rumble.com")) {
        return {
          success: false,
          error: "Invalid Rumble URL",
        };
      }

      const route = VideoController.rumble();

      const response = await axios.post(route.url, { link });
      const data = response.data;

      if (data.error) {
        return {
          success: false,
          error: data.error
        };
      }

      if (!Array.isArray(data) || data.length === 0) {
        return {
          success: false,
          error: "No sources found",
        };
      }

      // Ensure data conforms to VideoSource
      const sources: VideoSource[] = data.map((item: any) => ({
        file: item.file,
        type: item.type || 'video/mp4',
        label: item.label || 'HD',
        default: !!item.default
      }));

      return {
        success: true,
        sources: sources,
        jsonString: JSON.stringify(sources)
      };

    } catch (e: any) {
      const msg = e.response?.data?.error || e.message || "Unknown error";
      return {
        success: false,
        error: msg,
      };
    }
  }
  const fetchViaSaveTheVideo = async (
    link: string,
  ): Promise<VideoSource[] | null> => {
    try {
      const headers = {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36",
        Accept: "application/json, text/plain, */*",
        Origin: "https://savethevideo.net",
        Referer: "https://savethevideo.net/rumble-video-downloader?lang=en",
      };
      const url1 = `https://savethevideo.net/system/action.php?url=${encodeURIComponent(link)}`;
      const r1 = await request<string>(url1, { method: "GET", headers });
      let data: any = null;
      if (r1.success && r1.data && typeof r1.data === "string") {
        try {
          data = JSON.parse(r1.data);
        } catch {
          data = null;
        }
      }
      if (!data) {
        const body = new URLSearchParams({
          url: link,
          source: "rumble",
          lang: "en",
        }).toString();
        const r2 = await request<string>(
          "https://savethevideo.net/system/action.php",
          {
            method: "POST",
            headers: {
              ...headers,
              "Content-Type":
                "application/x-www-form-urlencoded; charset=UTF-8",
              "X-Requested-With": "XMLHttpRequest",
            },
            body,
          },
        );
        if (r2.success && r2.data && typeof r2.data === "string") {
          try {
            data = JSON.parse(r2.data);
          } catch {
            data = null;
          }
        }
      }
      if (!data || !data.links || !Array.isArray(data.links)) return null;
      const out: VideoSource[] = [];
      for (const it of data.links) {
        const u = String(it.url || "");
        const q = String(it.quality || "").toLowerCase();
        const type = String(it.type || "mp4").toLowerCase();
        if (!u) continue;
        const label = q || (type === "hls" ? "HLS" : "mp4");
        const t = type === "hls" || u.endsWith(".m3u8") ? "hls" : "video/mp4";
        out.push({ file: u, type: t, label });
      }
      return out.length ? out : null;
    } catch {
      return null;
    }
  }
  const fetchViaSaveTheVideoHtml = async (
    link: string,
  ): Promise<VideoSource[] | null> => {
    try {
      const origin = import.meta.env.VITE_STV_ORIGIN || "https://savethevideo.net";
      const referer =
        import.meta.env.VITE_STV_REFERER || `${origin}/rumble-video-downloader?lang=en`;
      const mod = await import("puppeteer");
      const p: any = (mod as any).default ?? mod;
      const headful =
        import.meta.env.VITE_STV_HEADFUL === "1" || import.meta.env.VITE_RUMBLE_HEADFUL === "1";
      const executablePath = import.meta.env.VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH;
      const argsEnv = import.meta.env.VITE_RUMBLE_PUPPETEER_ARGS;
      const argsBase = argsEnv
        ? argsEnv.split(/\s+/).filter(Boolean)
        : ["--no-sandbox", "--disable-dev-shm-usage"];
      const browser = await p.launch({
        headless: headful ? false : "new",
        executablePath: executablePath || undefined,
        args: argsBase,
      });
      try {
        const page = await browser.newPage();
        await page.setUserAgent(
          import.meta.env.VITE_STV_USER_AGENT ||
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36",
        );
        await page.setExtraHTTPHeaders({
          Referer: referer,
          Origin: origin,
          "Accept-Language":
            import.meta.env.VITE_STV_ACCEPT_LANGUAGE || "en-US,en;q=0.9",
        });

        await page.goto(
          `https://savethevideo.net/#url=${link}`,
          { waitUntil: "networkidle2" },
        );
        const btnStart = await page.waitForSelector('button[id="send"]', {
          timeout: 20000,
        });
        if (!btnStart) throw new Error("Could not find start button");

        try {
          await page.evaluate(() => {
            const el = document.querySelector('button[id="send"]') as HTMLElement | null;
            if (el) {
              try {
                el.click();
              } catch (e) {
                void 0;
              }
              try {
                el.dispatchEvent(new MouseEvent("click", { bubbles: true }));
              } catch (e) {
                void 0;
              }
              const jq: any = (window as any).jQuery || (window as any).$;
              if (jq) {
                try {
                  jq("#send").trigger("click");
                } catch (e) {
                  void 0;
                }
              }
            }
          });
        } catch (e) {
          void 0;
        }
        try {
          await page.click("#send");
        } catch (e) {
          void 0;
        }
        try {
          const box = await btnStart.boundingBox();
          if (box) {
            await page.mouse.move(
              box.x + box.width / 2,
              box.y + box.height / 2,
            );
            await page.mouse.down();
            await page.mouse.up();
          }
        } catch (e) {
          void 0;
        }
        try {
          await page.waitForResponse(
            (r: any) => {
              try {
                return (
                  typeof r.url === "function" &&
                  /\/system\/action\.php/i.test(r.url())
                );
              } catch (e) {
                return false;
              }
            },
            { timeout: 20000 },
          );
        } catch (e) {
          void 0;
        }
        await page.waitForSelector(".video-links a.btn-dl", { timeout: 20000 });
        const items = await page.$$eval(
          ".video-links a.btn-dl",
          (els: Element[]) =>
            els.map((el: Element) => ({
              href: (el as HTMLAnchorElement).getAttribute("href") || "",
              text: (el.textContent || "").trim(),
            })),
        );
        const sources: VideoSource[] = [];
        for (const it of items) {
          const href = it.href || "";
          if (!href) continue;
          const full = href.startsWith("http")
            ? href
            : `${origin}/${href.replace(/^\/?/, "")}`;
          try {
            const res = await fetch(full, {
              method: "GET",
              redirect: "manual",
              headers: {
                "User-Agent": import.meta.env.VITE_STV_USER_AGENT || "Mozilla/5.0",
                Referer: referer,
              } as any,
            } as any);
            const loc = (res as any)?.headers?.get
              ? (res as any).headers.get("location")
              : undefined;
            const file = loc || full;
            const qMatch = it.text.match(/(\d{3,4}p)/i);
            const label = qMatch ? qMatch[1] : "mp4";
            sources.push({
              file,
              type: file.endsWith(".m3u8") ? "hls" : "video/mp4",
              label,
            });
          } catch (e) {
            void 0;
          }
        }
        await browser.close();
        return sources.length ? sources : null;
      } catch (e) {
        try {
          await browser.close();
        } catch (err) {
          void 0;
        }
        return null;
      }
    } catch {
      return null;
    }
  }
  const fetchEmbedViaPuppeteer = async (
    videoId: string,
    link: string,
  ): Promise<string | null> => {
    try {
      const mod = await import("puppeteer");
      const p: any = (mod as any).default ?? mod;
      const headful =
        import.meta.env.VITE_RUMBLE_HEADFUL === "1" ||
        import.meta.env.VITE_RUMBLE_HEADLESS === "0";
      const executablePath = import.meta.env.VITE_RUMBLE_PUPPETEER_EXECUTABLE_PATH;
      const argsEnv = import.meta.env.VITE_RUMBLE_PUPPETEER_ARGS;
      const proxyServerSingle = import.meta.env.VITE_RUMBLE_PROXY_SERVER;
      const listRaw = import.meta.env.VITE_RUMBLE_PROXY_LIST || "";
      const listFromEnv = listRaw.split(/[\s,\n\r]+/).filter(Boolean);
      const proxies = proxyServerSingle
        ? [proxyServerSingle, ...listFromEnv]
        : listFromEnv.length
          ? listFromEnv
          : [""];
      for (const proxy of proxies) {
        const argsBase = argsEnv
          ? argsEnv.split(/\s+/).filter(Boolean)
          : ["--no-sandbox", "--disable-dev-shm-usage"];
        const args = proxy
          ? [...argsBase, `--proxy-server=${proxy}`]
          : argsBase;
        const browser = await p.launch({
          headless: headful ? false : "new",
          executablePath: executablePath || undefined,
          args,
        });
        try {
          const page = await browser.newPage();
          await page.setUserAgent(
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36",
          );
          try {
            await page.setExtraHTTPHeaders(buildHeaders(link, "page"));
          } catch (e) {
            void 0;
          }
          if (import.meta.env.VITE_RUMBLE_COOKIE) {
            try {
              const raw = String(import.meta.env.VITE_RUMBLE_COOKIE);
              const parts = raw
                .split(";")
                .map((s) => s.trim())
                .filter((s) => !!s && s.includes("="));
              const cookies = parts.map((p) => {
                const idx = p.indexOf("=");
                const name = p.slice(0, idx);
                const value = p.slice(idx + 1);
                return { name, value, domain: ".rumble.com", path: "/" } as any;
              });
              if (cookies.length) {
                await page.setCookie(...cookies);
              }
            } catch (e) {
              void 0;
            }
          }
          let proxyUser = import.meta.env.VITE_RUMBLE_PROXY_USERNAME;
          let proxyPass = import.meta.env.VITE_RUMBLE_PROXY_PASSWORD;
          if (proxy) {
            let authPart = "";
            const m1 = proxy.match(/\/\/([^@]+)@/);
            const m2 = proxy.match(/^([^@]+)@/);
            if (m1 && m1[1]) authPart = m1[1];
            else if (m2 && m2[1]) authPart = m2[1];
            if (authPart) {
              const parts = authPart.split(":");
              proxyUser = parts[0] || proxyUser;
              proxyPass = parts[1] || proxyPass;
            }
          }
          if (proxyUser && proxyPass) {
            await page.authenticate({
              username: proxyUser,
              password: proxyPass,
            });
          }
          const cookieHeader = import.meta.env.VITE_RUMBLE_COOKIE as string;
          if (cookieHeader) {
            const pairs = cookieHeader
              .split(";")
              .map((s) => s.trim())
              .filter(Boolean);
            const cookies = pairs.map((pair) => {
              const eq = pair.indexOf("=");
              const name = eq >= 0 ? pair.slice(0, eq) : pair;
              const value = eq >= 0 ? pair.slice(eq + 1) : "";
              return { name, value, domain: ".rumble.com", path: "/" } as any;
            });
            if (cookies.length > 0) {
              await page.setCookie(...cookies);
            }
          }

          const acceptLanguage =
            import.meta.env.VITE_RUMBLE_ACCEPT_LANGUAGE || "en-US,en;q=0.9";
          await page.setExtraHTTPHeaders({
            Referer: link,
            Origin: "https://rumble.com",
            "Accept-Language": acceptLanguage,
            "Sec-Fetch-Site": "same-origin",
            "Sec-Fetch-Mode": "cors",
            "Sec-Fetch-Dest": "empty",
          });
          const mediaUrls: string[] = [];
          page.on("response", async (r: any) => {
            try {
              const u = typeof r.url === "function" ? r.url() : "";
              if (/\.m3u8($|\?)/i.test(u) || /\.mp4($|\?)/i.test(u)) {
                if (!mediaUrls.includes(u)) mediaUrls.push(u);
              }
            } catch (e) {
              console.error(`Rumble response error: ${e}`);
            }
          });
          await page.goto(link, { waitUntil: "networkidle2" });
          try {
            await page.evaluate(() => {
              const btns = Array.from(
                document.querySelectorAll(
                  'button, [role="button"], .play, .btn',
                ),
              ) as HTMLElement[];
              for (const b of btns) {
                const text = (b.innerText || "").toLowerCase();
                if (text.includes("play")) {
                  try {
                    b.click();
                  } catch (e) {
                    void 0;
                  }
                }
              }
              const vids = Array.from(
                document.querySelectorAll("video"),
              ) as HTMLVideoElement[];
              for (const v of vids) {
                try {
                  v.muted = true;
                  v.play().catch(() => { });
                } catch (e) {
                  void 0;
                }
              }
            });
            await page.keyboard.press("Space");
            await new Promise((r) => setTimeout(r, 500));
            const box = await page.viewport();
            await page.mouse.click(
              Math.max(1, Math.floor(box.width / 2)),
              Math.max(1, Math.floor(box.height / 2)),
            );
            await new Promise((r) => setTimeout(r, 5000));
          } catch (e) {
            console.error(`Rumble click error: ${e}`);
          }

          const apiUrl = `https://rumble.com/embedJS/u3/?request=video&v=${videoId}&ext=%7B%22ad_count%22%3Anull%7D`;
          let text: string | null = null;
          try {
            text = await page.evaluate(async (url: string) => {
              try {
                const resp = await fetch(url, {
                  method: "GET",
                  credentials: "include",
                });
                if (!resp.ok) return null;
                return await resp.text();
              } catch {
                return null;
              }
            }, apiUrl);
          } catch {
            text = null;
          }

          if (!text) {
            const resp = await page.waitForResponse(
              (r: any) =>
                typeof r.url === "function" &&
                r.url().includes(`/embedJS/u3/?request=video&v=${videoId}`),
              { timeout: 120000 },
            );
            text = await resp.text();
          }
          // Fallback: if embed text is still empty, parse the full page HTML
          if (!text) {
            try {
              text = await page.content();
            } catch {
              text = null;
            }
          }
          // As a last resort, capture direct media responses (.m3u8/.mp4) and use the URL
          if (!text) {
            try {
              const mediaResp = await page.waitForResponse(
                (r: any) => {
                  try {
                    return (
                      typeof r.url === "function" &&
                      (/\.m3u8($|\?)/i.test(r.url()) ||
                        /\.mp4($|\?)/i.test(r.url()))
                    );
                  } catch (e) {
                    return false;
                  }
                },
                { timeout: 120000 },
              );
              text = (mediaResp as any)?.url ? (mediaResp as any).url() : null;
            } catch {
              text = null;
            }
          }
          if (!text && mediaUrls.length > 0) {
            text = mediaUrls.join("\n");
          }

          // Try the official embed page as another context
          if (!text) {
            const embedUrl = `https://rumble.com/embed/${videoId}/`;
            try {
              await page.goto(embedUrl, { waitUntil: "networkidle2" });
              try {
                await page.evaluate(() => {
                  const btns = Array.from(
                    document.querySelectorAll(
                      'button, [role="button"], .play, .btn',
                    ),
                  ) as HTMLElement[];
                  for (const b of btns) {
                    const text = (b.innerText || "").toLowerCase();
                    if (text.includes("play")) {
                      try {
                        b.click();
                      } catch (e) {
                        void 0;
                      }
                    }
                  }
                  const vids = Array.from(
                    document.querySelectorAll("video"),
                  ) as HTMLVideoElement[];
                  for (const v of vids) {
                    try {
                      v.muted = true;
                      v.play().catch(() => { });
                    } catch (e) {
                      void 0;
                    }
                  }
                });
                await page.keyboard.press("Space");
                await new Promise((r) => setTimeout(r, 5000));
              } catch (e) {
                console.error(`Rumble embed page error: ${e}`);
              }
              // Try fetching embedJS again from embed page
              const embedJsAgain = await page.evaluate(async (id: string) => {
                try {
                  const u = `https://rumble.com/embedJS/u3/?request=video&v=${id}&ext=%7B%22ad_count%22%3Anull%7D`;
                  const resp = await fetch(u, {
                    method: "GET",
                    credentials: "include",
                  });
                  if (!resp.ok) return null;
                  return await resp.text();
                } catch (e) {
                  return null;
                }
              }, videoId);
              if (embedJsAgain) {
                text = embedJsAgain as string;
              } else {
                // Scan page HTML for direct media URLs
                const html = await page.content();
                text = html || null;
              }
            } catch (err) {
              const errMsg = err instanceof Error ? err.message : String(err);
              console.error(`Rumble embed page error: ${errMsg}`);
              text = null;
            }
          }

          if (text) {
            const saved = await page.cookies("https://rumble.com");
            if (saved && saved.length) {
              const header = saved
                .map((c: any) => `${c.name}=${c.value}`)
                .join("; ");
              console.log(`Rumble cookies: ${header}`);
            }
            await browser.close();
            return text as string;
          }
          await browser.close();
        } catch (err) {
          try {
            await browser.close();
          } catch (e) {
            console.debug(String(e));
          }
        }
      }
      return null;
    } catch {
      return null;
    }
  }
  const parseVideoSources = (sourceContent: string): VideoSource[] => {
    const qualityMap = new Map<number, VideoSource>();
    try {
      const start = sourceContent.indexOf("{");
      const end = sourceContent.lastIndexOf("}");
      if (start >= 0 && end > start) {
        const raw = sourceContent.slice(start, end + 1);
        const parsed = JSON.parse(raw);
        const ua =
          parsed.ua ||
          (parsed.video && parsed.video.ua) ||
          (parsed.data && parsed.data.ua);
        if (ua && typeof ua === "object") {
          for (const [k, v] of Object.entries(ua)) {
            const q = parseInt(k, 10);
            const url = Array.isArray(v)
              ? (v[0] as string)
              : typeof v === "string"
                ? (v as string)
                : "";
            if (
              url &&
              url.endsWith(".mp4") &&
              QUALITY_MAP.value[q as keyof typeof QUALITY_MAP.value]
            ) {
              qualityMap.set(q, {
                file: url,
                type: "video/mp4",
                label: QUALITY_MAP.value[q as keyof typeof QUALITY_MAP.value],
                default: q === 720,
              });
            }
          }
        }
      }
    } catch {
      console.error("Error parsing video sources");
    }

    if (qualityMap.size === 0) {
      let m: RegExpExecArray | null;
      const pairRegex = /"(\d{3,4})"\s*:\s*\[\s*"(https?:[^"']+?\.mp4)"/g;
      while ((m = pairRegex.exec(sourceContent)) !== null) {
        const q = parseInt(m[1] || "0");
        const url = (m[2] ?? "") as string;
        if (!url) continue;
        if (QUALITY_MAP.value[q as keyof typeof QUALITY_MAP.value]) {
          qualityMap.set(q, {
            file: url,
            type: "video/mp4",
            label: QUALITY_MAP.value[q as keyof typeof QUALITY_MAP.value],
            default: q === 720,
          });
        }
      }
      // Additional fallback: look for objects with "url": "...mp4" and quality/q
      if (qualityMap.size === 0) {
        const urlQualityRegex =
          /"url"\s*:\s*"(https?:[^"']+?\.mp4)"[\s\S]*?"q(?:uality)?"\s*:\s*"?(\d{3,4})"?/g;
        while ((m = urlQualityRegex.exec(sourceContent)) !== null) {
          const url = (m[1] ?? "") as string;
          const q = parseInt((m[2] ?? "0") as string, 10);
          if (!url) continue;
          const label =
            QUALITY_MAP.value[q as keyof typeof QUALITY_MAP.value] || "unknown";
          qualityMap.set(q || 100, {
            file: url,
            type: "video/mp4",
            label,
            default: (q || 0) === 720,
          });
        }
      }
    }
    if (qualityMap.size === 0) {
      const anyUrls: { url: string; type: string }[] = [];
      const urlMp4Regex = /(https?:\/\/[^"']+?\.mp4)/g;
      const urlM3u8Regex = /(https?:\/\/[^"']+?\.m3u8)/g;
      let u: RegExpExecArray | null;
      while ((u = urlMp4Regex.exec(sourceContent)) !== null) {
        const url = (u[1] ?? "") as string;
        if (url) {
          if (!anyUrls.find((x) => x.url === url))
            anyUrls.push({ url, type: "video/mp4" });
        }
      }
      u = null;
      while ((u = urlM3u8Regex.exec(sourceContent)) !== null) {
        const url = (u[1] ?? "") as string;
        if (url) {
          if (!anyUrls.find((x) => x.url === url))
            anyUrls.push({ url, type: "hls" });
        }
      }
      anyUrls.forEach(({ url, type }, idx) => {
        qualityMap.set(100 + idx, {
          file: url,
          type: type,
          label: idx === 0 ? "unknown" : `unknown-${idx}`,
          default: idx === 0,
        });
      });
    }
    return Array.from(qualityMap.entries())
      .sort(([a], [b]) => b - a)
      .map(([_, s]) => s);
  }

  const download = async (
    url: string,
    outPath: string,
  ): Promise<{ success: boolean; error?: string }> => {
    try {
      const headers = {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36",
        Referer: "https://rumble.com",
        Accept: "*/*",
      };
      const res = await fetch(url, { method: "GET", headers });
      if (!res.ok || !res.body) {
        return {
          success: false,
          error: `HTTP ${res.status} ${res.statusText}`,
        };
      }
      const fsMod = await import("node:fs");
      const streamMod = await import("node:stream");
      const utilMod = await import("node:util");
      const pump = utilMod.promisify((streamMod as any).pipeline);
      const source = (streamMod as any).Readable.fromWeb(res.body as any);
      const dest = fsMod.createWriteStream(outPath);
      await pump(source, dest);
      return { success: true };
    } catch (e) {
      const msg = e instanceof Error ? e.message : "Unknown download error";
      return { success: false, error: msg };
    }
  }

  return {
    getVideoSources,
    buildHeaders,
    extractVideoIdFromUrl,
    extractVideoId,
    fetchViaSaveTheVideo,
    fetchViaSaveTheVideoHtml,
    fetchEmbedViaPuppeteer,
    parseVideoSources,
    download
  };
}

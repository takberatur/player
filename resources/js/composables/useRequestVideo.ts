
export const useRequestVideo = () => {
  const request = async<T>(url: string, options: RequestInit): Promise<RequestVideoResponse<T>> => {
    try {
      const response = await fetch(url, {
        method: options.method || 'GET',
        body: options.body || undefined,
        headers: options.headers || undefined
      });

      const contentType = response.headers.get('content-type') || '';
      let data: unknown = null;
      try {
        if (contentType.includes('application/json')) {
          data = await response.json();
        } else {
          data = await response.text();
        }
      } catch {
        data = null;
      }

      if (!response.ok) {
        const snippet = typeof data === 'string' ? (data as string).slice(0, 200) : '';
        return {
          success: false,
          message: response.statusText,
          data: undefined,
          error: `HTTP ${response.status} ${response.statusText}${snippet ? ' :: ' + snippet : ''}`
        };
      }

      return {
        success: true,
        message: response.statusText,
        data: data as T
      };
    } catch (error) {
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Unknown request error',
        data: undefined,
        error: error instanceof Error ? error.message : 'Unknown request error'
      };
    }
  };

  return {
    request
  }
}

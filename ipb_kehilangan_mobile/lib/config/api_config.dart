import 'package:flutter/foundation.dart';

class ApiConfig {
  static const String _apiBaseUrlOverride = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: '',
  );
  static const String _localBackendPort = '8000';
  static const String _androidEmulatorBaseUrl = 'http://10.0.2.2:8000/api';

  static String get baseUrl {
    if (_apiBaseUrlOverride.isNotEmpty) {
      return _apiBaseUrlOverride;
    }

    if (kIsWeb) {
      return _webBaseUrl;
    }

    if (defaultTargetPlatform == TargetPlatform.android) {
      return _androidEmulatorBaseUrl;
    }

    return _localhostBaseUrl;
  }

  static String get _webBaseUrl {
    final host = Uri.base.host;

    if (host.isEmpty || host == 'localhost') {
      return _localhostBaseUrl;
    }

    return 'http://$host:$_localBackendPort/api';
  }

  static String get _localhostBaseUrl {
    return 'http://127.0.0.1:$_localBackendPort/api';
  }
}

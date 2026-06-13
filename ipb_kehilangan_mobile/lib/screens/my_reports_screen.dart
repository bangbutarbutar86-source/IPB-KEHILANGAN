import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/theme_service.dart';
import 'report_detail_screen.dart';

class MyReportsScreen extends StatefulWidget {
  const MyReportsScreen({super.key});

  @override
  State<MyReportsScreen> createState() => MyReportsScreenState();
}

class MyReportsScreenState extends State<MyReportsScreen> {
  static const Color primaryBlue = Color(0xFF31479B);

  List reports = [];
  bool isLoading = true;
  String selectedFilter = "Lihat Semua";
  final searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    loadData();
  }

  Future<void> loadData() async {
    setState(() => isLoading = true);

    try {
      final results = await ApiService.getMyReports(
        search: searchController.text,
        filter: selectedFilter,
      );

      setState(() {
        reports = results;
        isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    }
  }

  @override
  void dispose() {
    searchController.dispose();
    super.dispose();
  }

  void searchReports() {
    loadData();
  }

  void updateFilter(String filter) {
    setState(() {
      selectedFilter = filter;
    });
    loadData();
  }

  void showFilterDialog() {
    showDialog(
      context: context,
      barrierColor: Colors.black26,
      builder: (_) => _ProfileFilterDialog(
        selectedFilter: selectedFilter,
        onSelected: (filter) {
          Navigator.pop(context);
          updateFilter(filter);
        },
      ),
    );
  }

  Future<void> openReportDetail(Map<String, dynamic> item) async {
    final id = (item['id'] ?? item['_id'] ?? '').toString();
    if (id.isEmpty) return;

    final shouldRefresh = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => ReportDetailScreen(
          reportId: id,
          initialReport: item,
          isOwnerView: true,
        ),
      ),
    );

    if (shouldRefresh == true && mounted) {
      loadData();
    }
  }

  Future<void> toggleReportStatus(Map<String, dynamic> item) async {
    final id = (item['id'] ?? item['_id'] ?? '').toString();
    if (id.isEmpty) return;

    final type = (item['type'] ?? 'Dicari').toString();
    final currentStatus = (item['status'] ?? '').toString();
    final nextStatus = currentStatus == "selesai" ? type.toLowerCase() : "selesai";

    try {
      await ApiService.updateReportStatus(id, nextStatus);
      await loadData();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<bool>(
      valueListenable: ThemeService.isDarkMode,
      builder: (context, isDarkMode, _) {
        return Scaffold(
          backgroundColor: isDarkMode ? const Color(0xFF111827) : Colors.white,
          appBar: AppBar(
            backgroundColor: isDarkMode ? const Color(0xFF1F2937) : Colors.white,
            elevation: 0,
            title: Text(
              "Laporan",
              style: TextStyle(
                color: isDarkMode ? Colors.white : Colors.black,
                fontSize: 25,
                fontWeight: FontWeight.w800,
              ),
            ),
            automaticallyImplyLeading: false, // Hide back button for main tab
          ),
          body: SafeArea(
            child: Column(
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                  child: _ProfileSearchBar(
                    controller: searchController,
                    onSearch: searchReports,
                    onFilter: showFilterDialog,
                    isDarkMode: isDarkMode,
                  ),
                ),
                Expanded(
                  child: isLoading
                      ? const Center(child: CircularProgressIndicator())
                      : reports.isEmpty
                          ? Center(
                              child: Text(
                                "Belum ada laporan",
                                style: TextStyle(
                                  color: isDarkMode
                                      ? const Color(0xFFB8B8B8)
                                      : const Color(0xFF777777),
                                ),
                              ),
                            )
                          : GridView.builder(
                              padding: const EdgeInsets.fromLTRB(16, 12, 16, 28),
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                childAspectRatio: 0.65,
                                crossAxisSpacing: 26,
                                mainAxisSpacing: 18,
                              ),
                              itemCount: reports.length,
                              itemBuilder: (context, index) {
                                final item = reports[index];
                                return _MyReportCard(
                                  item: item is Map
                                      ? Map<String, dynamic>.from(item)
                                      : const <String, dynamic>{},
                                  onStatusPressed: toggleReportStatus,
                                  onTap: openReportDetail,
                                  isDarkMode: isDarkMode,
                                );
                              },
                            ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _ProfileSearchBar extends StatelessWidget {
  const _ProfileSearchBar({
    required this.controller,
    required this.onSearch,
    required this.onFilter,
    required this.isDarkMode,
  });

  final TextEditingController controller;
  final VoidCallback onSearch;
  final VoidCallback onFilter;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Container(
            height: 42,
            decoration: BoxDecoration(
              color: isDarkMode
                  ? const Color(0xFF111827)
                  : const Color(0xFFD8D3D4),
              borderRadius: BorderRadius.circular(24),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x24000000),
                  blurRadius: 18,
                  offset: Offset(0, 8),
                ),
              ],
            ),
            child: TextField(
              controller: controller,
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => onSearch(),
              onChanged: (value) {
                if (value.trim().isEmpty) {
                  onSearch();
                }
              },
              style: TextStyle(
                color: isDarkMode ? Colors.white : Colors.black,
              ),
              decoration: InputDecoration(
                border: InputBorder.none,
                prefixIcon: IconButton(
                  onPressed: onSearch,
                  icon: const Icon(
                    Icons.search,
                    color: MyReportsScreenState.primaryBlue,
                    size: 28,
                  ),
                  tooltip: "Cari",
                ),
                contentPadding: const EdgeInsets.only(bottom: 8),
              ),
            ),
          ),
        ),
        const SizedBox(width: 22),
        IconButton(
          onPressed: onFilter,
          icon: Icon(
            Icons.tune,
            color: isDarkMode ? Colors.white : Colors.black,
            size: 34,
          ),
          tooltip: "Filter",
        ),
      ],
    );
  }
}

class _ProfileFilterDialog extends StatelessWidget {
  const _ProfileFilterDialog({
    required this.selectedFilter,
    required this.onSelected,
  });

  final String selectedFilter;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    const filters = [
      "Lihat Semua",
      "Dicari",
      "Ditemukan",
      "Selesai",
      "Belum Selesai",
    ];

    return Dialog(
      insetPadding: const EdgeInsets.symmetric(horizontal: 54),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(12, 18, 12, 14),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              "Filter Laporan",
              style: TextStyle(
                color: MyReportsScreenState.primaryBlue,
                fontSize: 17,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 12),
            for (final filter in filters)
              ListTile(
                dense: true,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                title: Text(
                  filter,
                  style: TextStyle(
                    color: selectedFilter == filter
                        ? MyReportsScreenState.primaryBlue
                        : Colors.black,
                    fontWeight: selectedFilter == filter
                        ? FontWeight.w800
                        : FontWeight.w500,
                  ),
                ),
                trailing: selectedFilter == filter
                    ? const Icon(
                        Icons.check,
                        color: MyReportsScreenState.primaryBlue,
                      )
                    : null,
                onTap: () => onSelected(filter),
              ),
          ],
        ),
      ),
    );
  }
}

class _MyReportCard extends StatelessWidget {
  const _MyReportCard({
    required this.item,
    required this.onStatusPressed,
    required this.onTap,
    required this.isDarkMode,
  });

  final Map<String, dynamic> item;
  final ValueChanged<Map<String, dynamic>> onStatusPressed;
  final ValueChanged<Map<String, dynamic>> onTap;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    final title = (item['title'] ?? '-').toString();
    final location = (item['location'] ?? '-').toString();
    final description = (item['description'] ?? '-').toString();
    final type = (item['type'] ?? 'Dicari').toString();
    final images = item['images'];
    final imageUrl =
        images is List && images.isNotEmpty ? images.first?.toString() : null;
    final status = (item['status'] ?? '').toString();
    final isDone = status == "selesai";

    return Material(
      color: isDarkMode ? const Color(0xFF1F2937) : Colors.white,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: () => onTap(item),
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            border: Border.all(
              color: isDarkMode
                  ? const Color(0xFF374151)
                  : const Color(0xFFD8D8D8),
              width: 3,
            ),
            borderRadius: BorderRadius.circular(18),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  _StatusPill(label: isDone ? "Selesai" : type),
                  const Spacer(),
                  Icon(
                    Icons.more_horiz,
                    size: 22,
                    color: isDarkMode ? Colors.white : Colors.black,
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: SizedBox(
                  height: 118,
                  width: double.infinity,
                  child: imageUrl != null && imageUrl.isNotEmpty
                      ? Image.network(
                          imageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) =>
                              const _CardImageFallback(),
                        )
                      : const _CardImageFallback(),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isDarkMode ? Colors.white : Colors.black,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 2),
              Row(
                children: [
                  Icon(
                    Icons.location_pin,
                    color: isDarkMode ? Colors.white : Colors.black,
                    size: 14,
                  ),
                  Expanded(
                    child: Text(
                      location,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: isDarkMode ? Colors.white : Colors.black,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Expanded(
                child: Text(
                  description,
                  maxLines: 4,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: isDarkMode ? Colors.white : Colors.black,
                    fontSize: 12,
                    height: 1.25,
                  ),
                ),
              ),
              SizedBox(
                width: double.infinity,
                height: 28,
                child: ElevatedButton(
                  onPressed: () => onStatusPressed(item),
                  style: ElevatedButton.styleFrom(
                    elevation: 0,
                    backgroundColor: const Color(0xFFD24A4A),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                    ),
                  ),
                  child: Text(
                    isDone ? "Belum Selesai" : "Tandai Selesai",
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    final normalized = label.toLowerCase();
    final color = normalized == "selesai"
        ? const Color(0xFF31CF2D)
        : normalized == "ditemukan"
            ? const Color(0xFFE6B31B)
            : const Color(0xFFD24A4A);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _CardImageFallback extends StatelessWidget {
  const _CardImageFallback();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF2F2F2),
      child: const Center(
        child: Icon(Icons.image_outlined, color: Color(0xFFBDBDBD), size: 44),
      ),
    );
  }
}

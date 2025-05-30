<template>
  <div class="leaderboard">
    <h2 class="mb-4">Creator Leaderboard</h2>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Creator</th>
            <th>Points</th>
            <th>Discount</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="creator in creators" :key="creator.rank">
            <td>#{{ creator.rank }}</td>
            <td>
              {{ creator.name }}
              <span v-if="creator.is_trending" class="badge bg-danger ms-2">🔥 Trending</span>
            </td>
            <td>{{ creator.points }}</td>
            <td>Rs. {{ creator.discount }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Leaderboard',
  data() {
    return {
      creators: [],
      loading: false,
      error: null
    }
  },
  methods: {
    async fetchLeaderboard() {
      this.loading = true;
      try {
        const response = await fetch('/api/leaderboard');
        if (!response.ok) throw new Error('Failed to fetch leaderboard');
        this.creators = await response.json();
      } catch (err) {
        this.error = err.message;
        console.error('Error fetching leaderboard:', err);
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.fetchLeaderboard();
    // Refresh every 5 minutes
    setInterval(this.fetchLeaderboard, 5 * 60 * 1000);
  }
}
</script>

<style scoped>
.leaderboard {
  padding: 1rem;
}
.table {
  margin-bottom: 0;
}
.badge {
  font-size: 0.8em;
}
</style> 